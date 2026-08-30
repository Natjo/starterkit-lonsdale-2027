#!/usr/bin/env node

const fs = require('fs');
const path = require('path');
const { spawnSync } = require('child_process');

const assetsRoot = path.resolve(__dirname, '../assets');
const ignoredDirectories = new Set(['js', 'vendors']);
const files = [];

function collect(directory) {
    for (const entry of fs.readdirSync(directory, { withFileTypes: true })) {
        if (entry.isDirectory() && ignoredDirectories.has(entry.name)) continue;

        const file = path.join(directory, entry.name);

        if (entry.isDirectory()) {
            collect(file);
        } else if (entry.isFile() && file.endsWith('.js')) {
            files.push(file);
        }
    }
}

collect(assetsRoot);

for (const file of files) {
    const result = spawnSync(process.execPath, ['--check', file], {
        encoding: 'utf8',
    });

    if (result.status !== 0) {
        process.stderr.write(result.stderr || result.stdout);
        process.exit(result.status || 1);
    }
}

console.log(`JavaScript syntax: OK (${files.length} files)`);
