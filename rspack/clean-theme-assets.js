#!/usr/bin/env node

const fs = require("fs");
const path = require("path");

const themeName = process.env.WP_THEME_NAME;

if (!themeName) {
    console.error("WP_THEME_NAME is required");
    process.exit(1);
}

const assetsDir = path.join(
    __dirname,
    "../web/wp-content/themes",
    themeName,
    "assets",
);

for (const dir of ["css", "js", "fonts", "video", "favicon", "img"]) {
    fs.rmSync(path.join(assetsDir, dir), { recursive: true, force: true });
    fs.mkdirSync(path.join(assetsDir, dir), { recursive: true });
}
