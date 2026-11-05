const fs = require('node:fs');
const path = require('node:path');

function validateThemeName(name = process.env.WP_THEME_NAME) {
    if (typeof name !== 'string' || !/^[A-Za-z0-9][A-Za-z0-9_-]*$/.test(name)) {
        throw new Error('WP_THEME_NAME doit être un nom technique sans chemin (lettres, chiffres, tirets ou underscores).');
    }
    return name;
}

/** Reject traversal and symlinks at every level under a trusted output root. */
function safePath(root, relative = '', { missing = false } = {}) {
    if (typeof relative !== 'string' || relative.includes('\\') || relative.includes('\0')
        || path.isAbsolute(relative) || relative.split('/').some(part => part === '..' || part === '.' || (relative !== '' && part === ''))) {
        throw new Error(`Chemin d’asset invalide : ${relative}`);
    }
    let current = path.resolve(root);
    const parts = relative === '' ? [] : relative.split('/');
    for (let index = -1; index < parts.length; index++) {
        if (index >= 0) current = path.join(current, parts[index]);
        let metadata;
        try { metadata = fs.lstatSync(current); }
        catch (error) { if (missing && error.code === 'ENOENT') continue; throw error; }
        if (metadata.isSymbolicLink() || ((index === -1 || index < parts.length - 1) && !metadata.isDirectory())) {
            throw new Error(`Chemin d’asset non sûr : ${current}`);
        }
    }
    return current;
}

function getThemeAssetsDirectory(themeName = process.env.WP_THEME_NAME) {
    const name = validateThemeName(themeName);
    const themes = path.resolve(__dirname, '../web/wp-content/themes');
    return safePath(themes, `${name}/assets`, { missing: true });
}

module.exports = { validateThemeName, safePath, getThemeAssetsDirectory };
