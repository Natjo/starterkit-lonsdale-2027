const fs = require("fs");
const path = require("path");

const APP_SCSS = path.resolve(__dirname, "../assets/app.scss");

const IMPORT_RE =
    /^@import\s+["']([^"']+)["'](?:\s+(?:critical|shared|split|once))*\s*;/;

const HERO_PREFIX = "heros/";

function parseImportFlags(line) {
    return [...line.matchAll(/\s+(critical|shared|split|once)\b/g)].map(
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

        if (
            flags.includes("split")
            || flags.includes("once")
            || isHeroCritical(importPath, flags)
        ) {
            modules.push(importPath);
        }
    }

    return modules;
}

function parseOnceImports(source) {
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

        const flags = parseImportFlags(trimmed);

        if (flags.includes("once")) {
            modules.push(match[1]);
        }
    }

    return modules;
}

function getSplitEntries() {
    if (!fs.existsSync(APP_SCSS)) {
        return {};
    }

    const source = fs.readFileSync(APP_SCSS, "utf8");
    const onceModules = new Set(parseOnceImports(source));
    const modules = parseOnDemandImports(source).filter(
        (modulePath) => !onceModules.has(modulePath),
    );
    const entries = {};

    for (const importPath of modules) {
        entries[importPath] = `./assets/${importPath}.scss?dispatch=split`;
    }

    return entries;
}

function getOnceEntries() {
    if (!fs.existsSync(APP_SCSS)) {
        return {};
    }

    const source = fs.readFileSync(APP_SCSS, "utf8");
    const entries = {};

    for (const importPath of parseOnceImports(source)) {
        entries[`once/${importPath}`] = `./assets/${importPath}.scss?dispatch=split`;
    }

    return entries;
}

module.exports = {
    APP_SCSS,
    parseOnDemandImports,
    parseOnceImports,
    parseSplitImports: parseOnDemandImports,
    getSplitEntries,
    getOnceEntries,
    isHeroCritical,
    HERO_PREFIX,
};
