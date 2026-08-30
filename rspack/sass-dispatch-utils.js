/**
 * Logique partagée pour la répartition des imports Sass.
 */

const fs = require("fs");
const path = require("path");

const IMPORT_RE =
    /^@import\s+["']([^"']+)["'](?:\s+(?:critical|shared|split))*\s*;/;

const SCSS_IMPORT_RE = /@import\s+["']([^"']+)["']/g;

const BREAKPOINTS_IMPORT = "styles/breakpoints";

const IGNORED_IMPORTS = new Set([
    BREAKPOINTS_IMPORT,
    "styles/settings",
]);

const HERO_PREFIX = "heros/";

function isHeroCriticalImport(importPath, flags) {
    return flags.includes("critical") && importPath.startsWith(HERO_PREFIX);
}

function isStylesPath(importPath) {
    return importPath.startsWith("styles/");
}

function parseImportLine(line) {
    const match = line.match(IMPORT_RE);

    if (!match) {
        return null;
    }

    const flags = [
        ...line.matchAll(/\s+(critical|shared|split)\b/g),
    ].map((entry) => entry[1]);

    return {
        path: match[1],
        flags,
    };
}

function dispatchAppScss(source, mode) {
    const critical = [];
    const app = [];
    const heroCritical = [];

    for (const line of source.split("\n")) {
        const trimmed = line.trim();

        if (trimmed === "" || trimmed.startsWith("//")) {
            continue;
        }

        const parsed = parseImportLine(trimmed);

        if (!parsed || IGNORED_IMPORTS.has(parsed.path)) {
            continue;
        }

        const isExplicitShared = parsed.flags.includes("shared");
        const isCritical = parsed.flags.includes("critical");
        const isSplit = parsed.flags.includes("split");

        if (isHeroCriticalImport(parsed.path, parsed.flags)) {
            heroCritical.push(parsed.path);
            continue;
        }

        if (isSplit) {
            continue;
        }

        if (isExplicitShared) {
            app.push(parsed.path);
            critical.push(parsed.path);
            continue;
        }

        if (isStylesPath(parsed.path)) {
            if (isCritical) {
                critical.push(parsed.path);
            } else {
                app.push(parsed.path);
            }

            continue;
        }

        if (isCritical) {
            critical.push(parsed.path);
            continue;
        }

        app.push(parsed.path);
    }

    if (mode === "critical") {
        return { bundle: critical, heroCritical };
    }

    if (mode === "app") {
        return { bundle: app, heroCritical };
    }

    return { bundle: [], heroCritical };
}

function resolveScssFile(importPath, loadPaths) {
    const hasExtension = path.extname(importPath) !== "";

    for (const loadPath of loadPaths) {
        const candidates = hasExtension
            ? [path.join(loadPath, importPath)]
            : [
                  path.join(loadPath, `${importPath}.scss`),
                  path.join(loadPath, `${importPath}.sass`),
                  path.join(loadPath, `${importPath}.css`),
                  path.join(loadPath, `${importPath}/index.scss`),
                  path.join(loadPath, `_${path.basename(importPath)}.scss`),
                  path.join(
                      loadPath,
                      path.dirname(importPath),
                      `_${path.basename(importPath)}.scss`,
                  ),
              ];

        for (const candidate of candidates) {
            if (fs.existsSync(candidate)) {
                return candidate;
            }
        }
    }

    return null;
}

function collectScssDependencyTree(entryImportPaths, loadPaths) {
    /** @type {Set<string>} */
    const files = new Set();
    /** @type {string[]} */
    const queue = [...entryImportPaths];

    while (queue.length > 0) {
        const importPath = queue.shift();
        const file = resolveScssFile(importPath, loadPaths);

        if (!file || files.has(file)) {
            continue;
        }

        files.add(file);

        const content = fs.readFileSync(file, "utf8");

        for (const match of content.matchAll(SCSS_IMPORT_RE)) {
            const nestedImport = match[1];

            if (
                nestedImport.startsWith("http://") ||
                nestedImport.startsWith("https://")
            ) {
                continue;
            }

            queue.push(nestedImport);
        }
    }

    return files;
}

function heroImportPathToName(importPath) {
    const match = importPath.match(/^heros\/hero-([^/]+)\/hero-\1$/);

    return match ? match[1] : importPath.replace(/^heros\//, "");
}

module.exports = {
    BREAKPOINTS_IMPORT,
    IGNORED_IMPORTS,
    HERO_PREFIX,
    IMPORT_RE,
    SCSS_IMPORT_RE,
    collectScssDependencyTree,
    dispatchAppScss,
    heroImportPathToName,
    isHeroCriticalImport,
    isStylesPath,
    parseImportLine,
    resolveScssFile,
};
