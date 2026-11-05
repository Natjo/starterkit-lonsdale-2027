/** app.scss is an index of local, extensionless imports, not a Sass stylesheet. */
function stripComments(source) {
    let result = '';
    let quote = '';
    for (let i = 0; i < source.length; i++) {
        const char = source[i];
        if (quote) {
            result += char;
            if (char === '\\' && i + 1 < source.length) result += source[++i];
            else if (char === quote) quote = '';
        } else if (char === '"' || char === "'") {
            quote = char;
            result += char;
        } else if (char === '/' && source[i + 1] === '/') {
            while (i < source.length && source[i] !== '\n') i++;
            result += '\n';
        } else if (char === '/' && source[i + 1] === '*') {
            result += ' ';
            i += 2;
            while (i < source.length && !(source[i] === '*' && source[i + 1] === '/')) {
                if (source[i] === '\n') result += '\n';
                i++;
            }
            if (i >= source.length) throw new Error('app.scss: commentaire non fermé.');
            i++;
        } else result += char;
    }
    return result;
}

function parseAppImports(source) {
    const input = stripComments(source);
    const imports = new Map();
    const instruction = /\s*@import\s+(["'])([A-Za-z0-9_-]+(?:\/[A-Za-z0-9_-]+)*)\1\s*(critical|shared|split|once)?\s*;/y;
    let offset = 0;
    while (input.slice(offset).trim() !== '') {
        instruction.lastIndex = offset;
        const match = instruction.exec(input);
        if (!match) {
            const line = input.slice(0, offset + input.slice(offset).search(/\S/)).split('\n').length;
            throw new Error(`app.scss:${line}: import invalide. Utiliser @import "dossier/fichier" avec un seul mode facultatif (critical, shared, split, once). Placer les règles CSS dans un fichier importé.`);
        }
        const item = { path: match[2], flags: match[3] ? [match[3]] : [] };
        const previous = imports.get(item.path);
        if (previous && previous.flags.join() !== item.flags.join()) {
            throw new Error(`app.scss: modes contradictoires pour ${item.path}.`);
        }
        imports.set(item.path, item);
        offset = instruction.lastIndex;
    }
    return [...imports.values()];
}

const HERO_PREFIX = 'heros/';
const isHeroCritical = (importPath, flags) => flags.includes('critical') && importPath.startsWith(HERO_PREFIX);
module.exports = { parseAppImports, stripComments, isHeroCritical, HERO_PREFIX };
