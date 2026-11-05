const fs = require("node:fs");
const path = require("node:path");

const CHUNK_FILE = /^([a-f0-9]{8}\.js)(?:\.map)?(?:\.gz|\.br)?$/;

function normalizeAssetName(name) {
    return String(name).replaceAll("\\", "/").replace(/^\.\//, "");
}

/** Only generated hash chunks and their sidecars; never sources or entrypoints. */
function pruneChunks(outputPath, assetNames) {
    const expected = new Set([...assetNames].map(normalizeAssetName));
    if (!expected.has("js/app.js")) throw new Error("Chunk cleanup requires the complete app compiler asset list.");
    const directory = path.join(outputPath, "js", "chunks");
    for (const current of [outputPath, path.join(outputPath, "js"), directory]) {
        let metadata;
        try { metadata = fs.lstatSync(current); }
        catch (error) {
            if (error.code === "ENOENT" && current === directory && ![...expected].some(name => /^js\/chunks\/[^/]+$/.test(name))) return [];
            throw error;
        }
        if (!metadata.isDirectory() || metadata.isSymbolicLink()) throw new Error(`Unsafe chunk directory: ${current}`);
    }
    for (const name of expected) {
        if (!/^js\/chunks\/[a-f0-9]{8}\.js$/.test(name)) continue;
        const metadata = fs.lstatSync(path.join(outputPath, name));
        if (!metadata.isFile() || metadata.isSymbolicLink()) throw new Error(`Active chunk missing or invalid: ${name}`);
    }
    const candidates = [];
    const entries = fs.readdirSync(directory);
    if (entries.length > 20000) throw new Error("Chunk directory exceeds the cleanup limit.");
    for (const file of entries) {
        const match = CHUNK_FILE.exec(file);
        if (!match || expected.has(`js/chunks/${match[1]}`) || expected.has(`js/chunks/${file}`)) continue;
        const target = path.join(directory, file);
        const metadata = fs.lstatSync(target);
        if (!metadata.isFile() || metadata.isSymbolicLink()) throw new Error(`Unsafe old chunk: ${file}`);
        candidates.push({ file, target, metadata });
    }
    const removed = [];
    for (const { file, target, metadata } of candidates) {
        const current = fs.lstatSync(target);
        if (!current.isFile() || current.isSymbolicLink() || current.ino !== metadata.ino
            || current.size !== metadata.size || current.mtimeMs !== metadata.mtimeMs) {
            throw new Error(`Chunk changed during cleanup: ${file}. ${removed.length} old file(s) already removed.`);
        }
        fs.unlinkSync(target);
        removed.push(`js/chunks/${file}`);
    }
    return removed;
}

/** One coordinator for all compilers sharing this output. Cached assets are included. */
class ChunkCleanupCoordinator {
    constructor(names, { finalize } = {}) {
        this.finalize = finalize;
        this.states = new Map(names.map(name => [name, { pending: true, successful: false, assets: new Set(), output: null }]));
    }
    begin(name) { this.states.get(name).pending = true; }
    fail(name) { Object.assign(this.states.get(name), { pending: false, successful: false }); }
    done(name, compilation, output, hasErrors = compilation.errors.length > 0) {
        const state = this.states.get(name);
        Object.assign(state, {
            pending: false, successful: !hasErrors,
            assets: new Set(compilation.getAssets().map(asset => asset.name)), output,
        });
        const states = [...this.states.values()];
        if (states.some(state => state.pending || !state.successful)) return [];
        if (new Set(states.map(state => path.resolve(state.output))).size !== 1) throw new Error("Chunk compilers must share one output directory.");
        const names = states.flatMap(state => [...state.assets]);
        const deferCleanup = process.env.RSPACK_DEFER_CHUNK_CLEANUP === "1";
        if (this.finalize) return this.finalize(output, names, { compilers: [...this.states.keys()], deferCleanup });
        return deferCleanup ? [] : pruneChunks(output, names);
    }
}

class PruneChunksPlugin {
    constructor(coordinator, name) { this.coordinator = coordinator; this.name = name; }
    apply(compiler) {
        const label = "PruneChunksPlugin";
        const begin = () => this.coordinator.begin(this.name);
        compiler.hooks.invalid.tap(label, begin);
        compiler.hooks.watchRun.tap(label, begin);
        compiler.hooks.run.tap(label, begin);
        compiler.hooks.thisCompilation.tap(label, begin);
        compiler.hooks.failed.tap(label, () => this.coordinator.fail(this.name));
        compiler.hooks.done.tap(label, stats => {
            const removed = this.coordinator.done(this.name, stats.compilation, compiler.options.output.path, stats.hasErrors());
            if (removed.length) compiler.getInfrastructureLogger(label).info(`Removed ${removed.length} obsolete build asset(s).`);
        });
    }
}

module.exports = { pruneChunks, ChunkCleanupCoordinator, PruneChunksPlugin };
