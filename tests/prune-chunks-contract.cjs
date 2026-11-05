const test = require("node:test");
const assert = require("node:assert/strict");
const fs = require("node:fs");
const os = require("node:os");
const path = require("node:path");
const { pruneChunks, ChunkCleanupCoordinator, PruneChunksPlugin } = require("../rspack/prune-chunks");
const rspack = require("@rspack/core");
function fixture(t) {
    const root = fs.mkdtempSync(path.join(os.tmpdir(), "rspack-chunks-"));
    fs.mkdirSync(path.join(root, "js/chunks"), { recursive: true });
    t.after(() => fs.rmSync(root, { recursive: true, force: true }));
    return root;
}
const compilation = (names, errors = []) => ({ errors, getAssets: () => names.map(name => ({ name })) });
test("prunes only obsolete generated chunks and companions", t => {
    const root = fixture(t);
    for (const file of ["11111111.js", "11111111.js.map", "aaaaaaaa.js", "aaaaaaaa.js.map", "aaaaaaaa.js.gz", "orphan.js", "notes.txt"]) fs.writeFileSync(path.join(root, "js/chunks", file), "keep or discard");
    fs.writeFileSync(path.join(root, "js/app.js"), "entry");
    const removed = pruneChunks(root, ["./js/app.js", "./js/chunks/11111111.js"]);
    assert.equal(removed.length, 3);
    for (const file of ["11111111.js", "11111111.js.map", "orphan.js", "notes.txt"]) assert.ok(fs.existsSync(path.join(root, "js/chunks", file)));
    assert.ok(fs.existsSync(path.join(root, "js/app.js")));
});
test("waits for all compilers, preserves cached assets, and blocks after errors or invalidation", t => {
    const root = fixture(t);
    for (const file of ["11111111.js", "22222222.js", "aaaaaaaa.js"]) fs.writeFileSync(path.join(root, "js/chunks", file), "chunk");
    const coordinator = new ChunkCleanupCoordinator(["app", "admin"]);
    assert.deepEqual(coordinator.done("app", compilation(["js/app.js", "js/chunks/11111111.js"]), root), []);
    assert.ok(fs.existsSync(path.join(root, "js/chunks/aaaaaaaa.js")));
    assert.deepEqual(coordinator.done("admin", compilation(["js/admin.js", "js/chunks/22222222.js"], [new Error("failed")]), root), []);
    assert.ok(fs.existsSync(path.join(root, "js/chunks/aaaaaaaa.js")));
    assert.equal(coordinator.done("admin", compilation(["js/admin.js", "js/chunks/22222222.js"]), root).length, 1);
    coordinator.begin("app");
    fs.writeFileSync(path.join(root, "js/chunks/aaaaaaaa.js"), "old");
    assert.deepEqual(coordinator.done("admin", compilation(["js/admin.js", "js/chunks/22222222.js"]), root), []);
    assert.equal(coordinator.done("app", compilation(["js/app.js", "js/chunks/11111111.js"]), root).length, 1);
    assert.ok(fs.existsSync(path.join(root, "js/chunks/22222222.js")), "admin not rebuilt, cached assets still protected");
});
test("missing active chunks or symlinks fail before deleting any candidate", t => {
    const root = fixture(t);
    fs.writeFileSync(path.join(root, "js/chunks/aaaaaaaa.js"), "old");
    assert.throws(() => pruneChunks(root, ["js/app.js", "js/chunks/11111111.js"]));
    assert.ok(fs.existsSync(path.join(root, "js/chunks/aaaaaaaa.js")));
    fs.symlinkSync(path.join(root, "js/chunks/aaaaaaaa.js"), path.join(root, "js/chunks/bbbbbbbb.js"));
    assert.throws(() => pruneChunks(root, ["js/app.js"]));
    assert.ok(fs.existsSync(path.join(root, "js/chunks/aaaaaaaa.js")));
});
test("real multi-compiler build retains both compilers' async chunks and removes old files", async t => {
    const root = fixture(t);
    fs.writeFileSync(path.join(root, "app.js"), "import('./a.js');");
    fs.writeFileSync(path.join(root, "admin.js"), "import('./b.js');");
    fs.writeFileSync(path.join(root, "a.js"), "export const a = 1;");
    fs.writeFileSync(path.join(root, "b.js"), "export const b = 2;");
    fs.writeFileSync(path.join(root, "js/chunks/aaaaaaaa.js"), "old");
    const coordinator = new ChunkCleanupCoordinator(["app", "admin"]);
    const compiler = rspack(["app", "admin"].map(name => ({ name, context: root, mode: "production", entry: { [name]: `./${name}.js` }, output: { path: root, filename: "js/[name].js", chunkFilename: "js/chunks/[contenthash:8].js", clean: false }, plugins: [new PruneChunksPlugin(coordinator, name)] })));
    const stats = await new Promise((resolve, reject) => compiler.run((error, stats) => compiler.close(closeError => error || closeError ? reject(error || closeError) : resolve(stats))));
    assert.equal(stats.hasErrors(), false, stats.toString());
    const names = stats.stats.flatMap(stat => stat.compilation.getAssets().map(asset => asset.name));
    assert.equal(names.filter(name => /^js\/chunks\/.+\.js$/.test(name)).length, 2);
    for (const name of names) assert.ok(fs.existsSync(path.join(root, name)));
    assert.ok(!fs.existsSync(path.join(root, "js/chunks/aaaaaaaa.js")));
});

test("real watch rebuild prunes replaced hashes but retains another compiler's cached chunks", { timeout: 20000 }, async t => {
    const root = fs.mkdtempSync(path.join(os.tmpdir(), "rspack-chunks-watch-"));
    const output = path.join(root, "output");
    for (const name of ["app", "admin"]) fs.writeFileSync(path.join(root, `${name}.js`), `import('./${name}-async.js').then(value => console.log(value));`);
    fs.writeFileSync(path.join(root, "app-async.js"), 'export const title = "first";');
    fs.writeFileSync(path.join(root, "admin-async.js"), 'export const title = "admin stays cached";');
    const coordinator = new ChunkCleanupCoordinator(["app", "admin"]);
    const compiler = rspack(["app", "admin"].map(name => ({ name, context: root, mode: "production", entry: { [name]: `./${name}.js` }, output: { path: output, filename: "js/[name].js", chunkFilename: "js/chunks/[contenthash:8].js", clean: false }, plugins: [new PruneChunksPlugin(coordinator, name)] })));
    let watching; let firstHashes;
    t.after(async () => {
        if (watching) await new Promise(resolve => watching.close(() => compiler.close(resolve)));
        fs.rmSync(root, {recursive:true, force:true});
    });
    await new Promise((resolve, reject) => {
        const deadline = setTimeout(() => reject(new Error("Watch rebuild timeout")), 15000);
        watching = compiler.watch({aggregateTimeout:20,poll:50}, (error, stats) => {
            if (error || stats.hasErrors()) { clearTimeout(deadline); reject(error || new Error(stats.toString())); return; }
            // MultiStats can contain only the compiler rebuilt on this watch tick.
            const hashes = [...coordinator.states.values()].flatMap(state => [...state.assets]).filter(name => /^js\/chunks\/.+\.js$/.test(name));
            if (!firstHashes) {
                firstHashes = hashes;
                setTimeout(() => fs.writeFileSync(path.join(root, "app-async.js"), 'export const title = "second changed version";'), 100);
                return;
            }
            if (hashes.every(name => firstHashes.includes(name))) return;
            try {
                assert.equal(hashes.length, 2);
                for (const name of hashes) assert.ok(fs.existsSync(path.join(output, name)));
                const replaced = firstHashes.filter(name => !hashes.includes(name));
                assert.equal(replaced.length, 1);
                assert.ok(!fs.existsSync(path.join(output, replaced[0])));
                clearTimeout(deadline); resolve();
            } catch (failure) { clearTimeout(deadline); reject(failure); }
        });
    });
});
