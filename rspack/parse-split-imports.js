const fs = require("fs");
const path = require("path");

const APP_SCSS = path.resolve(__dirname, "../assets/app.scss");

const IMPORT_RE =
    /^@import\s+["']([^"']+)["'](?:\s+(?:critical|shared|split))*\s*;/;

const HERO_PREFIX = "heros/";

function parseImportFlags(line) {
    return [...line.matchAll(/\s+(critical|shared|split)\b/g)].map(
        (entry) => entry[1],
    );
}

function isHeroCritical(importPath, flags) {
    return flags.includes("critical") && importPath.startsWith(HERO_PREFIX);
}

function parseOnDemandImports(source) {
    const modules = [];

    for (const line of source.split("\n")) {
        const trimmed = line.trim();

        if (trimmed === "" || trimmed.startsWith("//")) {
            continue;
        }

        const match = trimmed.match(IMPORT_RE);

        if (!match) {
            continue;
        }

        const importPath = match[1];
        const flags = parseImportFlags(trimmed);

        if (flags.includes("split") || isHeroCritical(importPath, flags)) {
            modules.push(importPath);
        }
    }

    return modules;
}

function getSplitEntries() {
    if (!fs.existsSync(APP_SCSS)) {
        return {};
    }

    const source = fs.readFileSync(APP_SCSS, "utf8");
    const modules = parseOnDemandImports(source);
    const entries = {};

    for (const importPath of modules) {
        entries[importPath] = `./assets/${importPath}.scss?dispatch=split`;
    }

    return entries;
}

module.exports = {
    APP_SCSS,
    parseOnDemandImports,
    parseSplitImports: parseOnDemandImports,
    getSplitEntries,
    isHeroCritical,
    HERO_PREFIX,
};
