/**
 * Logique partagée pour la répartition des imports Sass.
 */

const fs = require("fs");
const path = require("path");

const BREAKPOINTS_IMPORT = "styles/breakpoints";

const IGNORED_IMPORTS = new Set([
    BREAKPOINTS_IMPORT,
    "styles/settings",
]);

const { parseAppImports, isHeroCritical: isHeroCriticalImport } = require("./sass-imports");

function dispatchAppScss(source, mode) {
    const critical = [];
    const app = [];
    const heroCritical = [];

    for (const parsed of parseAppImports(source)) {
        if (IGNORED_IMPORTS.has(parsed.path)) continue;

        const isExplicitShared = parsed.flags.includes("shared");
        const isCritical = parsed.flags.includes("critical");
        const isSplit = parsed.flags.includes("split");
        const isOnce = parsed.flags.includes("once");

        if (isHeroCriticalImport(parsed.path, parsed.flags)) {
            heroCritical.push(parsed.path);
            continue;
        }

        if (isSplit || isOnce) {
            continue;
        }

        if (isExplicitShared) {
            app.push(parsed.path);
            critical.push(parsed.path);
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

module.exports = {
    BREAKPOINTS_IMPORT,
    dispatchAppScss,
    resolveScssFile,
};
