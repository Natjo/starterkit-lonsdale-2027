// Bundled with Forge's own Markdown dependencies, executed only in the preview's Node container.
import fs from 'node:fs';
import path from 'node:path';
import MarkdownIt from 'markdown-it';
import hljs from 'highlight.js';
const [source, output] = process.argv.slice(2);
if (!source || !output) throw new Error('Source and output are required');
const markdown = new MarkdownIt({ html: false, highlight(text, language) {
    return hljs.highlight(text, { language: hljs.getLanguage(language) ? language : 'plaintext', ignoreIllegals: true }).value;
} });
markdown.core.ruler.push('design-system-headings', state => {
    for (const token of state.tokens) {
        if (['heading_open', 'heading_close'].includes(token.type)) token.tag = `h${Math.min(6, Number(token.tag.slice(1)) + 3)}`;
        if (token.type === 'inline') for (const child of token.children || []) {
            if (child.type !== 'link_open') continue;
            const href = child.attrGet('href') || '';
            const match = /^\.\.\/([a-z0-9_-]+)\/README\.md$/i.exec(href);
            if (match) child.attrSet('href', `#${state.env.family === 'common' ? `common-${match[1]}` : state.env.family === 'modules' ? `module-${match[1]}` : /^(card|strate|hero)-/.test(match[1]) ? match[1] : `component-${match[1]}`}`);
            else if (/^\.\.\/README\.md(?:#.*)?$/.test(href)) child.attrSet('href', '#family-documentation');
        }
    }
});
function render() {
fs.mkdirSync(output, { recursive: true });
for (const family of ['components', 'cards', 'strates', 'heros', 'common', 'modules']) {
    const directory = path.join(source, 'assets', family);
    if (!fs.existsSync(directory)) continue;
    for (const entry of fs.readdirSync(directory, { withFileTypes: true })) {
        if (!entry.isDirectory() || !/^[a-z0-9_-]+$/i.test(entry.name)) continue;
        const readme = path.join(directory, entry.name, 'README.md');
        if (!fs.existsSync(readme)) continue;
        const target = path.join(output, family, entry.name);
        fs.mkdirSync(target, { recursive: true });
        fs.writeFileSync(path.join(target, 'README.html'), markdown.render(fs.readFileSync(readme, 'utf8'), { family }));
    }
}
const breaks = fs.readFileSync(path.join(source, 'assets/styles/breakpoints.scss'), 'utf8');
const points = [...breaks.matchAll(/^\$breakpoint-([a-z]+):\s*(\d+)px;\s*$/gm)].map(([, name, width]) => ({ name, width: Number(width), only: breaks.includes(`$breakpoint-${name}-only:`) }));
if (!points.length || points.length !== (breaks.match(/^\$breakpoint-[a-z]+:/gm) || []).length) throw new Error('Unsupported breakpoints in starter');
fs.writeFileSync(path.join(output, 'breakpoints.json'), JSON.stringify(points.sort((a,b) => a.width-b.width)));

// Background utilities belong to the current theme; do not duplicate their colors.
const classesPath = path.join(source, 'assets/styles/classes.scss');
const classesSource = fs.existsSync(classesPath) ? fs.readFileSync(classesPath, 'utf8').replace(/\/\*[\s\S]*?\*\/|\/\/[^\n]*/g, '') : '';
const backgrounds = [...new Set([...classesSource.matchAll(/\.(bg-[a-z0-9_-]+)/gi)].map(match => match[1]))].sort();
fs.writeFileSync(path.join(output, 'backgrounds.json'), JSON.stringify(backgrounds));


// Derive the available project catalogue from actual PHP files, even in Clean mode.
const themes = fs.readdirSync(path.join(source, 'web/wp-content/themes'), { withFileTypes: true })
    .filter(entry => entry.isDirectory() && fs.existsSync(path.join(source, 'web/wp-content/themes', entry.name, 'template-parts/components')));
if (themes.length !== 1) throw new Error('Expected one theme for the design-system catalogue');
const catalogue = {};
const packageInfo = JSON.parse(fs.readFileSync(path.join(source, 'package.json'), 'utf8'));
catalogue.starterVersion = typeof packageInfo.version === 'string' ? packageInfo.version.trim() : '';
for (const [family, prefix] of [['components',''], ['cards','card-'], ['strates','strate-'], ['heros','hero-'], ['common','']]) {
    const folder = path.join(source, 'web/wp-content/themes', themes[0].name, 'template-parts', family);
    catalogue[family] = {};
    if (!fs.existsSync(folder)) continue;
    for (const entry of fs.readdirSync(folder).sort()) {
        if (!entry.endsWith('.php') || !entry.startsWith(prefix)) continue;
        const slug = entry.slice(prefix.length, -4);
        if (!/^[a-z0-9_-]+$/i.test(slug)) continue;
        const module = prefix + slug;
        const manifestPath = path.join(source, 'assets', family, module, `${module}.json`);
        const manifest = fs.existsSync(manifestPath) ? JSON.parse(fs.readFileSync(manifestPath, 'utf8')) : {};
        catalogue[family][slug] = { version: typeof manifest.version === 'string' ? manifest.version.trim() : '', name: manifest.name || slug, defaults: manifest.defaults || {}, headerConfiguration: manifest.headerConfiguration || null, footerConfiguration: manifest.footerConfiguration || null };
        if (family === 'cards') {
            const columns = manifest.designSystem?.columns;
            catalogue[family][slug].designSystem = { columns: Number.isInteger(columns) && columns >= 1 && columns <= 3 ? columns : 3 };
        }
        if (family === 'strates') {
            const margin = manifest.designSystem?.options?.margin;
            const defaults = {};
            if (['', 'sm', 'md', 'lg'].includes(margin?.top)) defaults.top = margin.top;
            if (['0', 'sm', 'md', 'lg'].includes(margin?.bottom)) defaults.bottom = margin.bottom;
            catalogue[family][slug].designSystem = { options: { margin: defaults } };
        }
    }
}
// Modules are derived from the current source tree, also after Clean removed manifests.
catalogue.modules = {};
const modulesDirectory = path.join(source, 'assets/modules');
if (fs.existsSync(modulesDirectory)) for (const entry of fs.readdirSync(modulesDirectory, { withFileTypes: true }).sort((a,b) => a.name.localeCompare(b.name))) {
    if (!entry.isDirectory() || !/^[a-z0-9_-]+$/i.test(entry.name)) continue;
    const folder = path.join(modulesDirectory, entry.name);
    const manifestPath = path.join(folder, entry.name + '.json');
    const manifest = fs.existsSync(manifestPath) ? JSON.parse(fs.readFileSync(manifestPath, 'utf8')) : {};
    const files = fs.readdirSync(folder);
    const candidates = [manifest.file, entry.name + '.js', entry.name + '.mjs', ...files];
    const file = candidates.find(file => typeof file === 'string' && /^[a-z0-9_-]+\.m?js$/i.test(file) && fs.statSync(path.join(folder, file), { throwIfNoEntry: false })?.isFile());
    if (!file && manifest.type !== 'module') continue;
    const dependencies = {};
    for (const family of ['components','cards','strates','heroes','common','modules','vendors','menus']) {
        const values = manifest.dependencies?.[family];
        if (Array.isArray(values)) dependencies[family] = values.filter(value => typeof value === 'string' && /^[a-z0-9_-]+$/i.test(value));
    }
    catalogue.modules[entry.name] = { version: typeof manifest.version === 'string' ? manifest.version.trim() : '', name: typeof manifest.name === 'string' ? manifest.name : entry.name, file: file || '', hasCss: files.some(file => file === entry.name + '.scss' || file === entry.name + '.css'), dependencies };
}
fs.writeFileSync(path.join(output, 'catalogue.json'), JSON.stringify(catalogue));

}
render();

if (process.argv.includes('--watch')) {
    fs.writeFileSync('/tmp/forge-design-system-docs-ready', 'ready');
    let timer;
    const roots = [path.join(source, 'assets')];
    const themesRoot = path.join(source, 'web/wp-content/themes');
    for (const theme of fs.readdirSync(themesRoot)) {
        for (const folder of ['template-parts', 'acf-json']) roots.push(path.join(themesRoot, theme, folder));
    }
    for (const directory of roots.filter(folder => fs.existsSync(folder))) {
        fs.watch(directory, { recursive: true }, (_event, filename) => {
            if (filename && !/(README\.md|\.json|\.php|breakpoints\.scss|classes\.scss|\.m?js)$/i.test(String(filename))) return;
            clearTimeout(timer);
            timer = setTimeout(() => { try { render(); } catch (error) { console.error('Design system documentation:', error); } }, 200);
        }).on('error', error => console.error('Design system watcher:', error));
    }
}
