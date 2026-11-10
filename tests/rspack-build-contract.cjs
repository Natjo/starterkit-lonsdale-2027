const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const os = require('node:os');
const path = require('node:path');
const { spawnSync } = require('node:child_process');
const rspack = require('@rspack/core');
const { parseAppImports } = require('../rspack/sass-imports');
const { dispatchAppScss } = require('../rspack/sass-dispatch-utils');
const { parseOnDemandImports } = require('../rspack/parse-split-imports');
const { safePath, validateThemeName } = require('../rspack/output-safety');
const { BuildOutputLock } = require('../rspack/build-lock');
const { publishInventory, readInventory, verifyInventory } = require('../rspack/asset-inventory');
const { ChunkCleanupCoordinator } = require('../rspack/prune-chunks');
const repo = path.resolve(__dirname, '..');
function fixture(t) {
    const root = fs.mkdtempSync(path.join(os.tmpdir(), 'rspack-build-'));
    t.after(() => fs.rmSync(root, { recursive: true, force: true }));
    return root;
}
function put(root, name, data = 'content') {
    const file = path.join(root, name);
    fs.mkdirSync(path.dirname(file), { recursive: true });
    fs.writeFileSync(file, data);
}
const finalize = (output, names, options) => publishInventory(output, names, { ...options, mode: 'production' });
const compilation = (names, errors = []) => ({ errors, getAssets: () => names.map(name => ({ name })) });

test('one parser respects comments, multiline imports and multiple instructions, without reading flags from comments', () => {
    const input = '/*\n@import "hidden/a" once;\n*/\n@import\n "a/a" critical; @import "b/b"; // once\n@import "c/c" split;';
    assert.deepEqual(parseAppImports(input).map(item => item.path), ['a/a', 'b/b', 'c/c']);
    assert.deepEqual(parseOnDemandImports(input), ['c/c']);
    assert.deepEqual(dispatchAppScss(input, 'app').bundle, ['b/b']);
    assert.deepEqual(dispatchAppScss(input, 'critical').bundle, ['a/a']);
    assert.equal(parseAppImports('@import "a/a" once; @import "a/a" once;').length, 1);
    for (const invalid of ['p {color:red}', '@import "a/a" once split;', '@import "../outside";', '@import "a/a" once; @import "a/a";', '/* unclosed']) {
        assert.throws(() => parseAppImports(invalid), /app.scss/);
    }
});

test('theme slugs and asset paths cannot escape their roots', t => {
    const root = fixture(t);
    for (const name of ['', '../x', '/tmp/site', 'foo/bar', 'foo\\bar']) assert.throws(() => validateThemeName(name));
    assert.equal(validateThemeName('client-2027'), 'client-2027');
    for (const name of ['../x', '/tmp/x', 'css/../../x', 'css\\file', 'css//file']) assert.throws(() => safePath(root, name, { missing: true }));
});

test('inventory prunes CSS, copied assets and entry maps only after global success; manual files survive', t => {
    const root = fixture(t);
    for (const name of ['js/app.js', 'css/once/pages/old/old.css', 'img/old.svg', 'css/app.css.map']) put(root, name);
    finalize(root, ['js/app.js', 'css/once/pages/old/old.css', 'img/old.svg', 'css/app.css.map'], { compilers: ['app', 'split'] });
    put(root, 'img/manual.svg');
    const coordinator = new ChunkCleanupCoordinator(['app', 'split'], { finalize });
    coordinator.done('app', compilation(['js/app.js']), root);
    coordinator.done('split', compilation([], [new Error('broken Sass')]), root);
    assert.ok(fs.existsSync(path.join(root, 'img/old.svg')));
    assert.ok(fs.existsSync(path.join(root, 'css/once/pages/old/old.css')));
    assert.equal(readInventory(root).files.length, 4, 'failed build does not publish an incomplete inventory');
    assert.equal(coordinator.done('split', compilation([]), root).length, 3);
    assert.ok(fs.existsSync(path.join(root, 'img/manual.svg')));
    verifyInventory(root);
    put(root, 'js/app.js', 'corrupted');
    assert.throws(() => verifyInventory(root), /modifié/);
});

test('root and child symlinks prevent cleanup before any deletion', t => {
    const root = fixture(t);
    const outside = fixture(t);
    put(root, 'js/app.js');
    put(outside, 'pages/old/old.css');
    fs.symlinkSync(outside, path.join(root, 'css'));
    assert.throws(() => finalize(root, ['js/app.js'], { compilers: ['app'] }), /non sûr/);
    assert.ok(fs.existsSync(path.join(outside, 'pages/old/old.css')));
    fs.unlinkSync(path.join(root, 'css'));
    put(root, 'css/pages/old/old.css');
    fs.symlinkSync(outside, path.join(root, 'css/z-link'));
    assert.throws(() => finalize(root, ['js/app.js'], { compilers: ['app'] }), /non sûr/);
    assert.ok(fs.existsSync(path.join(root, 'css/pages/old/old.css')));
});

test('a second process cannot acquire a build or clean lock; close releases it', t => {
    const root = fixture(t);
    const lock = new BuildOutputLock();
    lock.acquire(root, 'app');
    lock.acquire(root, 'split');
    const program = `const {BuildOutputLock}=require(${JSON.stringify(path.join(repo, 'rspack/build-lock'))});const lock=new BuildOutputLock();lock.acquire(${JSON.stringify(root)},'other');lock.release();`;
    const blocked = spawnSync(process.execPath, ['-e', program], { encoding: 'utf8' });
    assert.equal(blocked.status, 1);
    assert.match(blocked.stderr, /utilise déjà/);
    lock.close('app');
    assert.ok(fs.existsSync(path.join(root, '.rspack-build.lock')));
    lock.close('split');
    assert.equal(spawnSync(process.execPath, ['-e', program]).status, 0);
});

function project(t, development) {
    const root = fixture(t);
    fs.cpSync(path.join(repo, 'rspack'), path.join(root, 'rspack'), { recursive: true });
    fs.symlinkSync(path.join(repo, 'node_modules'), path.join(root, 'node_modules'));
    put(root, 'assets/app.scss', '@import "styles/example" critical; @import "styles/other";');
    put(root, 'assets/styles/example.scss', '.example { color: red; }');
    put(root, 'assets/styles/other.scss', '.other { color: blue; }');
    put(root, 'assets/styles/_breakpoints.scss', '$breakpoint-lg-up: "(min-width: 800px)";');
    put(root, 'assets/app.js', 'console.log("fixture");');
    put(root, 'assets/styles/utils/admin.scss', '.admin{color:red}');
    put(root, 'assets/styles/utils/admin-rte.scss', '.rte{color:red}');
    const oldMode = process.env.NODE_ENV;
    const oldTheme = process.env.WP_THEME_NAME;
    process.env.NODE_ENV = development ? 'development' : 'production';
    process.env.WP_THEME_NAME = 'test';
    let configs;
    try { configs = require(path.join(root, 'rspack/rspack.base')).createRspackConfigs(); }
    finally {
        if (oldMode === undefined) delete process.env.NODE_ENV; else process.env.NODE_ENV = oldMode;
        if (oldTheme === undefined) delete process.env.WP_THEME_NAME; else process.env.WP_THEME_NAME = oldTheme;
    }
    for (const config of configs) {
        config.context = root;
        config.mode = development ? 'development' : 'production';
        config.cache = false;
        config.devtool = development ? 'source-map' : false;
        config.output.path = path.join(root, 'output');
        config.optimization.minimize = false;
        config.infrastructureLogging = { level: 'none' };
        config.resolveLoader = { modules: [path.join(repo, 'node_modules')] };
    }
    return { root, configs };
}

test('real multi-compiler watch supports zero → one → zero on-demand imports, including breakpoint injection', { timeout: 25000 }, async t => {
    const { root, configs } = project(t, true);
    assert.ok(configs.some(config => config.name === 'split'));
    put(root, 'assets/components/test/test.scss', '.test { @media #{$breakpoint-lg-up} { color: red; } }');
    const compiler = rspack(configs);
    let watching;
    t.after(async () => { if (watching) await new Promise(resolve => watching.close(() => compiler.close(resolve))); });
    await new Promise((resolve, reject) => {
        const timeout = setTimeout(() => reject(new Error('watch timeout')), 20000);
        let step = 0;
        const css = path.join(root, 'output/css/once/components/test/test.css');
        watching = compiler.watch({ poll: 50, aggregateTimeout: 25 }, (error, stats) => {
            if (error || stats.hasErrors()) { clearTimeout(timeout); reject(error || new Error(stats.toString({ all: false, errors: true }))); return; }
            try {
                if (step === 0) {
                    verifyInventory(path.join(root, 'output'));
                    step = 1;
                    setTimeout(() => fs.appendFileSync(path.join(root, 'assets/app.scss'), '\n@import "components/test/test" once;'), 100);
                } else if (step === 1 && fs.existsSync(css)) {
                    assert.match(fs.readFileSync(css, 'utf8'), /min-width/);
                    step = 2;
                    setTimeout(() => fs.writeFileSync(path.join(root, 'assets/app.scss'), '@import "styles/example" critical; @import "styles/other";'), 100);
                } else if (step === 2 && !fs.existsSync(css)) {
                    verifyInventory(path.join(root, 'output'));
                    clearTimeout(timeout); resolve();
                }
            } catch (failure) { clearTimeout(timeout); reject(failure); }
        });
    });
});

test('a real failing split compiler preserves previous CSS and inventory', { timeout: 15000 }, async t => {
    const { root, configs } = project(t, false);
    const output = path.join(root, 'output');
    put(output, 'js/app.js', 'old');
    put(output, 'css/once/pages/old/old.css', '.old{}');
    finalize(output, ['js/app.js', 'css/once/pages/old/old.css'], { compilers: ['app', 'split'] });
    const previous = fs.readFileSync(path.join(output, '.rspack-assets.json'), 'utf8');
    configs.find(config => config.name === 'split').entry = { broken: './assets/missing.scss' };
    const compiler = rspack(configs);
    const stats = await new Promise((resolve, reject) => compiler.run((error, stats) => compiler.close(closeError => error || closeError ? reject(error || closeError) : resolve(stats))));
    assert.equal(stats.hasErrors(), true);
    assert.ok(fs.existsSync(path.join(output, 'css/once/pages/old/old.css')));
    assert.equal(fs.readFileSync(path.join(output, '.rspack-assets.json'), 'utf8'), previous);
});

test('the historical Forge flag defers chunks, while validated CSS changes are applied immediately', t => {
    const root = fixture(t);
    put(root, 'js/app.js');
    put(root, 'js/chunks/11111111.js');
    put(root, 'css/once/pages/old/old.css');
    finalize(root, ['js/app.js', 'js/chunks/11111111.js', 'css/once/pages/old/old.css'], { compilers: ['app'] });
    finalize(root, ['js/app.js'], { compilers: ['app'], deferCleanup: true });
    verifyInventory(root);
    assert.ok(fs.existsSync(path.join(root, 'js/chunks/11111111.js')));
    assert.equal(fs.existsSync(path.join(root, 'css/once/pages/old/old.css')), false, 'old once CSS must not override new normal CSS in PHP');
    assert.equal(readInventory(root).pending.length, 1);
    finalize(root, ['js/app.js'], { compilers: ['app'] });
    assert.equal(fs.existsSync(path.join(root, 'js/chunks/11111111.js')), false);
    assert.equal(readInventory(root).pending.length, 0);
    put(root, 'js/chunks/22222222.js');
    finalize(root, ['js/app.js', 'js/chunks/22222222.js'], { compilers: ['app'] });
    finalize(root, ['js/app.js'], { compilers: ['app'], deferCleanup: true });
    fs.unlinkSync(path.join(root, 'js/chunks/22222222.js')); // Forge's own cleanup.
    finalize(root, ['js/app.js'], { compilers: ['app'], deferCleanup: true });
    assert.equal(readInventory(root).pending.length, 0, 'deleted Forge chunks do not accumulate in pending');
});

test('inventory checks every compiler chunk, including an async chunk owned by admin', t => {
    const root = fixture(t);
    put(root, 'js/app.js');
    put(root, 'js/chunks/11111111.js');
    finalize(root, ['js/app.js', 'js/chunks/11111111.js'], { compilers: ['app', 'admin'] });
    fs.unlinkSync(path.join(root, 'js/chunks/11111111.js'));
    assert.throws(() => verifyInventory(root), /ENOENT/);
});

test('empty generated CSS/JS stubs are valid and old style entry stubs are pruned', t => {
    const root = fixture(t);
    put(root, 'js/admin.js', '');
    put(root, 'css/app.css', '');
    put(root, 'js/once/pages/removed/removed.js', 'old');
    finalize(root, ['js/admin.js', 'css/app.css'], { compilers: ['app', 'admin'] });
    assert.equal(fs.existsSync(path.join(root, 'js/once/pages/removed/removed.js')), false);
    verifyInventory(root);
});

test('real production configuration supports a site without any normal CSS import', { timeout: 15000 }, async t => {
    const { root, configs } = project(t, false);
    put(root, 'assets/app.scss', '@import "styles/example" critical;');
    const compiler = rspack(configs);
    const stats = await new Promise((resolve, reject) => compiler.run((error, stats) => compiler.close(closeError => error || closeError ? reject(error || closeError) : resolve(stats))));
    assert.equal(stats.hasErrors(), false, stats.toString({ all: false, errors: true }));
    verifyInventory(path.join(root, 'output'));
    assert.ok(fs.existsSync(path.join(root, 'output/css/app.css')), 'the required app.css exists even when empty');
});

test('dev/prod configurations choose their Sass and watch modes independently of NODE_ENV or require cache', t => {
    const { root } = project(t, false);
    const development = require(path.join(root, 'rspack/rspack.dev'));
    const production = require(path.join(root, 'rspack/rspack.prod'));
    assert.equal(typeof development.find(config => config.name === 'split').entry, 'function');
    assert.equal(typeof production.find(config => config.name === 'split').entry, 'object');
    assert.notEqual(development[0].cache, false);
    assert.equal(production[0].cache, false);
    const sass = config => config.module.rules.find(rule => rule.oneOf).oneOf[0].use.find(item => item.loader === 'sass-loader');
    assert.equal(sass(development[0]).options.sourceMap, true);
    assert.equal(sass(production[0]).options.sourceMap, false);
});


test('the starter build does not publish Forge styleguide documentation', { timeout: 15000 }, async t => {
    const { root, configs } = project(t, false);
    put(root, 'assets/components/btn/README.md', '# Button');
    put(root, 'assets/styles/_breakpoints.scss', '$breakpoint-sm: 576px;');
    const compiler = rspack(configs);
    const stats = await new Promise((resolve, reject) => compiler.run((error, stats) => compiler.close(closeError => error || closeError ? reject(error || closeError) : resolve(stats))));
    assert.equal(stats.hasErrors(), false, stats.toString({ all: false, errors: true }));
    assert.ok(!fs.existsSync(path.join(root, 'output/styleguide')));
    verifyInventory(path.join(root, 'output'));
});
