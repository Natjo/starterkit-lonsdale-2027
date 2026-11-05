#!/usr/bin/env node

const fs = require("fs");
const path = require("path");
const { getOnceEntries, getSplitEntries } = require("./parse-split-imports");
const { validateThemeName, getThemeAssetsDirectory } = require("./output-safety");
const { verifyInventory } = require("./asset-inventory");

validateThemeName();
const assetsDirectory = process.env.THEME_ASSETS_DIR
    ? path.resolve(process.env.THEME_ASSETS_DIR)
    : getThemeAssetsDirectory();
const inventory = verifyInventory(assetsDirectory);
for (const compiler of ["critical", "app", "admin", "split"]) {
    if (!inventory.compilers.includes(compiler)) throw new Error(`Inventaire incomplet : compilateur ${compiler} absent.`);
}
const ownedFiles = new Set(inventory.files.map(entry => entry.name));
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

const invalidFiles = requiredFiles.filter(relativePath => !ownedFiles.has(relativePath));
if (invalidFiles.length > 0) {
    console.error(`Build assets incomplet : fichier(s) non produit(s) par la compilation\n- ${invalidFiles.join("\n- ")}`);
    process.exit(1);
}
console.log(`Build assets: OK (${inventory.files.length} fichiers vérifiés par inventaire et SHA-256)`);
