const fs = require('node:fs');
const path = require('node:path');
const { createHash, randomUUID } = require('node:crypto');
const { safePath } = require('./output-safety');

const INVENTORY_FILE = '.rspack-assets.json';
const ROOTS = /^(css|js|fonts|video|favicon|img|lottie)\//;
const MODULES = '(?:cards|common|components|heros|modules|pages|strates|vendors)';
const LEGACY_CSS = new RegExp(`^css/(?:once/)?${MODULES}/.+\\.css(?:\\.map)?$`);
const LEGACY_STYLE_JS = new RegExp(`^js/(?:once/)?${MODULES}/.+\\.js(?:\\.map)?$`);
const LEGACY_CHUNK = /^js\/chunks\/[a-f0-9]{8}\.js(?:\.map)?(?:\.gz|\.br)?$/;
const LEGACY_MAP = /^(?:css|js)\/(?:app|critical|admin|admin-rte)\.(?:css|js)\.map$/;
const digest = buffer => createHash('sha256').update(buffer).digest('hex');

function assetPath(output, name, options) {
    if (typeof name !== 'string' || !ROOTS.test(name)) throw new Error(`Nom d’asset invalide : ${name}`);
    return safePath(output, name, options);
}

function readInventory(output, { optional = false } = {}) {
    const file = safePath(output, INVENTORY_FILE, { missing: optional });
    if (optional && !fs.existsSync(file)) return null;
    const data = JSON.parse(fs.readFileSync(file, 'utf8'));
    if (!data || data.schemaVersion !== 1 || !Array.isArray(data.files) || data.files.length > 20000
        || !Array.isArray(data.compilers) || !data.compilers.every(name => typeof name === 'string')
        || (data.pending !== undefined && !Array.isArray(data.pending))
        || data.files.length + (data.pending?.length || 0) > 20000) {
        throw new Error('Inventaire Rspack invalide.');
    }
    const names = new Set();
    for (const entry of [...data.files, ...(data.pending || [])]) {
        if (!entry || typeof entry !== 'object') throw new Error('Entrée d’inventaire Rspack invalide.');
        assetPath(output, entry.name, { missing: true });
        if (names.has(entry.name) || !Number.isSafeInteger(entry.size) || entry.size < 0 || typeof entry.sha256 !== 'string' || !/^[a-f0-9]{64}$/.test(entry.sha256)) {
            throw new Error('Entrée d’inventaire Rspack invalide.');
        }
        names.add(entry.name);
    }
    return data;
}

function describeAsset(output, name) {
    const file = assetPath(output, name);
    const metadata = fs.lstatSync(file);
    if (!metadata.isFile()) {
        throw new Error(`Asset absent ou non régulier : ${name}`);
    }
    const buffer = fs.readFileSync(file);
    return { name, size: buffer.length, sha256: digest(buffer) };
}

function verifyInventory(output, data = readInventory(output)) {
    for (const entry of data.files) {
        const actual = describeAsset(output, entry.name);
        if (actual.size !== entry.size || actual.sha256 !== entry.sha256) throw new Error(`Asset modifié depuis la compilation : ${entry.name}`);
    }
    return data;
}

/** Bounded traversal, with no symlink (including the css/js root) followed. */
function collectLegacyFiles(output) {
    const found = [];
    let count = 0;
    function visit(relative) {
        const target = safePath(output, relative, { missing: true });
        if (!fs.existsSync(target)) return;
        const metadata = fs.lstatSync(target);
        if (++count > 20000) throw new Error('Trop de fichiers à examiner pour le nettoyage Rspack.');
        if (metadata.isDirectory()) {
            for (const entry of fs.readdirSync(target)) visit(`${relative}/${entry}`);
        } else if (metadata.isFile()) found.push(relative);
        else throw new Error(`Fichier non régulier dans les sorties Rspack : ${relative}`);
    }
    visit('css');
    visit('js');
    return found;
}

/** Validate every candidate before the first deletion; preserve unowned manual assets. */
function planCleanup(output, active, previous) {
    const candidates = new Set((previous?.files || []).map(entry => entry.name));
    for (const name of collectLegacyFiles(output)) {
        if (LEGACY_CSS.test(name) || LEGACY_STYLE_JS.test(name) || LEGACY_CHUNK.test(name) || LEGACY_MAP.test(name)) candidates.add(name);
    }
    const plan = [];
    for (const name of candidates) {
        if (active.has(name)) continue;
        // Keep manually compressed companions of an active chunk.
        if (/\.(gz|br)$/.test(name) && active.has(name.replace(/(?:\.map)?\.(gz|br)$/, ''))) continue;
        const file = assetPath(output, name, { missing: true });
        if (!fs.existsSync(file)) continue;
        const metadata = fs.lstatSync(file);
        if (!metadata.isFile()) throw new Error(`Asset obsolète non régulier : ${name}`);
        plan.push({ name, metadata });
    }
    return plan;
}

function removePlanned(output, plan) {
    const removed = [];
    for (const { name, metadata } of plan) {
        const file = assetPath(output, name);
        const current = fs.lstatSync(file);
        if (!current.isFile() || current.ino !== metadata.ino || current.size !== metadata.size || current.mtimeMs !== metadata.mtimeMs) {
            throw new Error(`Asset modifié pendant le nettoyage : ${name}`);
        }
        fs.unlinkSync(file);
        removed.push(name);
    }
    return removed;
}

function publishInventory(output, names, { compilers, mode, deferCleanup = false }) {
    if (names.length > 20000) throw new Error('Inventaire Rspack trop volumineux.');
    const active = new Set([...names].map(name => name.replace(/^\.\//, '')));
    const files = [...active].sort().map(name => describeAsset(output, name));
    const previous = readInventory(output, { optional: true });
    // Preserve the historical Forge flag: only chunk deletion is deferred, never CSS.
    const pending = [...(previous?.files || []), ...(previous?.pending || [])]
        .filter(entry => !active.has(entry.name));
    for (const entry of pending) assetPath(output, entry.name, { missing: true });
    const plan = planCleanup(output, active, { files: pending });
    const deferred = entry => deferCleanup && LEGACY_CHUNK.test(entry.name);
    const deferredNames = new Set(plan.filter(deferred).map(entry => entry.name));
    const data = { schemaVersion: 1, mode, compilers, files, pending: [...new Map(pending.filter(entry => deferredNames.has(entry.name)).map(entry => [entry.name, entry])).values()] };
    if (files.length + data.pending.length > 20000) throw new Error('Inventaire Rspack trop volumineux.');
    const target = safePath(output, INVENTORY_FILE, { missing: true });
    const temporary = safePath(output, `.rspack-assets-${randomUUID()}.tmp`, { missing: true });
    fs.writeFileSync(temporary, JSON.stringify(data, null, 2) + '\n', { flag: 'wx' });
    try {
        const removed = removePlanned(output, plan.filter(entry => !deferred(entry)));
        fs.renameSync(temporary, target);
        return removed;
    } finally {
        if (fs.existsSync(temporary)) fs.unlinkSync(temporary);
    }
}

module.exports = { INVENTORY_FILE, readInventory, verifyInventory, publishInventory, planCleanup, removePlanned };
