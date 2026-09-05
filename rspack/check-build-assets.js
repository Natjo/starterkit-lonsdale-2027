#!/usr/bin/env node

const fs = require("fs");
const path = require("path");
const { getOnceEntries, getSplitEntries } = require("./parse-split-imports");

const themeName = process.env.WP_THEME_NAME;

if (!themeName) {
    console.error("WP_THEME_NAME is required");
    process.exit(1);
}

const assetsDirectory = process.env.THEME_ASSETS_DIR
    ? path.resolve(process.env.THEME_ASSETS_DIR)
    : path.resolve(
          __dirname,
          "../web/wp-content/themes",
          themeName,
          "assets",
      );
const requiredFiles = [
    "css/critical.css",
    "css/app.css",
    "css/admin.css",
    "css/admin-rte.css",
    "js/app.js",
    ...Object.keys(getSplitEntries()).map((modulePath) => `css/${modulePath}.css`),
    ...Object.keys(getOnceEntries()).map((modulePath) => `css/${modulePath}.css`),
];
const copiedDirectories = ["fonts", "video", "favicon", "img"];
const lottieSourceDirectory = path.resolve(__dirname, "../assets/components/lottie");
const ignoredCopiedFiles = new Set([".DS_Store", ".gitkeep", "head-snippet.html"]);

function collectCopiedFiles(directory, prefix = "") {
    if (!fs.existsSync(directory)) {
        return [];
    }

    const files = [];

    for (const entry of fs.readdirSync(directory, { withFileTypes: true })) {
        const relativePath = prefix === "" ? entry.name : `${prefix}/${entry.name}`;
        const file = path.join(directory, entry.name);

        if (entry.isDirectory()) {
            files.push(...collectCopiedFiles(file, relativePath));
        } else if (entry.isFile() && !ignoredCopiedFiles.has(entry.name)) {
            files.push(relativePath);
        }
    }

    return files;
}

for (const directory of copiedDirectories) {
    const sourceDirectory = path.resolve(__dirname, `../assets/${directory}`);

    for (const relativePath of collectCopiedFiles(sourceDirectory)) {
        requiredFiles.push(`${directory}/${relativePath}`);
    }
}

for (const relativePath of collectCopiedFiles(lottieSourceDirectory)) {
    if (path.extname(relativePath).toLowerCase() === ".json") {
        requiredFiles.push(`lottie/${relativePath}`);
    }
}

const invalidFiles = requiredFiles.filter((relativePath) => {
    const file = path.join(assetsDirectory, relativePath);

    return !fs.existsSync(file) || !fs.statSync(file).isFile() || fs.statSync(file).size === 0;
});

const appFile = path.join(assetsDirectory, "js/app.js");
const missingChunks = [];

if (fs.existsSync(appFile)) {
    const appSource = fs.readFileSync(appFile, "utf8");
    const chunkHashes = new Set(
        [...appSource.matchAll(/\b\d+:\s*["']([a-f0-9]{8})["']/g)]
            .map((match) => match[1]),
    );

    for (const hash of chunkHashes) {
        const chunk = path.join(assetsDirectory, `js/chunks/${hash}.js`);

        if (
            !fs.existsSync(chunk)
            || !fs.statSync(chunk).isFile()
            || fs.statSync(chunk).size === 0
        ) {
            missingChunks.push(`js/chunks/${hash}.js`);
        }
    }
}

invalidFiles.push(...missingChunks);

if (invalidFiles.length > 0) {
    console.error(
        `Build assets incomplet : fichier(s) absent(s) ou vide(s)\n- ${invalidFiles.join("\n- ")}`,
    );
    process.exit(1);
}

console.log(`Build assets: OK (${requiredFiles.length} fichiers + chunks dynamiques)`);
