/**
 * Dispatch SCSS imports before sass-loader runs.
 *
 * `styles/breakpoints` is auto-injected in app, critical and split (Sass only).
 *
 * Syntax in app.scss:
 *   @import "styles/styles" critical;                    → critical only
 *   @import "components/slider/slider";                  → app only
 *   @import "heros/hero-homepage/hero-homepage" critical; → bundle dédié, inline si hero()
 *   @import "pages/404" once;                             → bundle dédié, inline au rendu
 *
 * Entries:
 *   app.scss?dispatch=app       → app.css
 *   app.scss?dispatch=critical  → critical.css
 *   module.scss?dispatch=split → css/{module-path}.css
 */

const path = require("path");

const {
    BREAKPOINTS_IMPORT,
    dispatchAppScss,
    resolveScssFile,
} = require("../sass-dispatch-utils");

function toImport(importPath) {
    return `@import "${importPath}";`;
}

function addDispatchedDependencies(loaderContext, dispatchedImports) {
    const loadPaths = [path.resolve(loaderContext.context || process.cwd())];

    for (const importPath of dispatchedImports) {
        const resolved = resolveScssFile(importPath, loadPaths);

        if (resolved) {
            loaderContext.addDependency(resolved);
        }
    }
}

function resolveMode(context) {
    const fromOptions = context.getOptions().mode;

    if (
        fromOptions === "app" ||
        fromOptions === "critical" ||
        fromOptions === "split"
    ) {
        return fromOptions;
    }

    const query = context.resourceQuery || "";
    const match = query.match(/(?:^|\?)dispatch=(app|critical|split)(?:&|$)/);

    return match ? match[1] : null;
}

function isAppScssResource(resourcePath) {
    return path.basename(resourcePath).replace(/\?.*$/, "") === "app.scss";
}

module.exports = function sassDispatchLoader(source) {
    const mode = resolveMode(this);

    if (mode !== "app" && mode !== "critical" && mode !== "split") {
        return source;
    }

    this.addDependency(this.resourcePath);

    if (mode === "split" && !isAppScssResource(this.resourcePath)) {
        const dispatchedImports = [BREAKPOINTS_IMPORT];
        addDispatchedDependencies(this, dispatchedImports);

        return `@import "${BREAKPOINTS_IMPORT}";\n${source}`;
    }

    if (mode !== "app" && mode !== "critical") {
        return source;
    }

    const { bundle } = dispatchAppScss(source, mode);
    const dispatchedImports = [...new Set([BREAKPOINTS_IMPORT, ...bundle])];
    addDispatchedDependencies(this, dispatchedImports);

    if (dispatchedImports.length === 0) {
        return "/* sass-dispatch: empty */\n";
    }

    return `${dispatchedImports.map(toImport).join("\n")}\n`;
};
