#!/usr/bin/env node
// Never erase the last working build before recompiling.
const { getThemeAssetsDirectory } = require('./output-safety');
const { BuildOutputLock } = require('./build-lock');
const { readInventory, verifyInventory, publishInventory } = require('./asset-inventory');
const output = getThemeAssetsDirectory();
const lock = new BuildOutputLock();
lock.acquire(output, 'clean');
try {
    const inventory = verifyInventory(output, readInventory(output));
    const removed = publishInventory(output, inventory.files.map(entry => entry.name), {
        compilers: inventory.compilers, mode: inventory.mode,
    });
    console.log(`Nettoyage Rspack : ${removed.length} ancien(s) fichier(s) retiré(s).`);
} finally { lock.release(); }
