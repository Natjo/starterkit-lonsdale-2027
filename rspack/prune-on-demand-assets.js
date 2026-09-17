const fs = require("fs");
const path = require("path");
const { getOnceEntries, getSplitEntries } = require("./parse-split-imports");

const ON_DEMAND_ROOTS = new Set([
    "cards",
    "common",
    "components",
    "heros",
    "modules",
    "pages",
    "strates",
]);

function collectFiles(directory) {
    if (!fs.existsSync(directory)) return [];

    return fs.readdirSync(directory, { withFileTypes: true }).flatMap((entry) => {
        const file = path.join(directory, entry.name);
        return entry.isDirectory() ? collectFiles(file) : [file];
    });
}

function removeEmptyDirectories(directory, root) {
    if (!fs.existsSync(directory)) return;

    for (const entry of fs.readdirSync(directory, { withFileTypes: true })) {
        if (entry.isDirectory()) removeEmptyDirectories(path.join(directory, entry.name), root);
    }

    if (directory !== root && fs.readdirSync(directory).length === 0) {
        fs.rmdirSync(directory);
    }
}

function pruneOnDemandAssets(assetsDirectory) {
    const cssDirectory = path.join(assetsDirectory, "css");
    if (!fs.existsSync(cssDirectory)) return [];

    const expected = new Set([
        ...Object.keys(getSplitEntries()).map((entry) => `${entry}.css`),
        ...Object.keys(getOnceEntries()).map((entry) => `${entry}.css`),
    ]);
    const removed = [];

    for (const file of collectFiles(cssDirectory)) {
        if (!file.endsWith(".css") && !file.endsWith(".css.map")) continue;

        const relative = path.relative(cssDirectory, file).split(path.sep).join("/");
        const cssRelative = relative.endsWith(".map") ? relative.slice(0, -4) : relative;
        const parts = cssRelative.split("/");
        const root = parts[0] === "once" ? parts[1] : parts[0];

        if (!ON_DEMAND_ROOTS.has(root) || expected.has(cssRelative)) continue;

        fs.rmSync(file, { force: true });
        removed.push(relative);
    }

    removeEmptyDirectories(cssDirectory, cssDirectory);
    return removed;
}

module.exports = { pruneOnDemandAssets };

