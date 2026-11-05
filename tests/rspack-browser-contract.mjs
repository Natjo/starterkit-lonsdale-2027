import assert from 'node:assert/strict';
import { createServer } from 'node:http';
import { readFile, mkdtemp, rm } from 'node:fs/promises';
import { spawn } from 'node:child_process';
import { tmpdir } from 'node:os';
import path from 'node:path';
import { createRequire } from 'node:module';
const require = createRequire(import.meta.url);
const { getThemeAssetsDirectory, safePath } = require('../rspack/output-safety');
const { verifyInventory } = require('../rspack/asset-inventory');
const assets = getThemeAssetsDirectory();
verifyInventory(assets);
const prefix = `/wp-content/themes/${process.env.WP_THEME_NAME}/assets/`;
const requests = [];
const missing = [];
const html = `<!doctype html><html><head><meta charset="utf-8">
<script>window.testErrors=[];addEventListener('error',e=>testErrors.push(e.message));addEventListener('unhandledrejection',e=>testErrors.push(String(e.reason)));</script>
<script defer src="${prefix}js/app.js"></script></head><body>
<div class="accordion" data-module="components/accordion" data-context="@visible true">
<div class="details"><h3><button class="summary" aria-expanded="false" aria-controls="panel">Ouvrir</button></h3>
<div id="panel" class="details-content" aria-hidden="true">Contenu</div></div></div>
<pre id="result">PENDING</pre><script>
const timer=setInterval(()=>{const root=document.querySelector('.accordion');if(root.dataset.hydrated!=='true')return;
clearInterval(timer);const button=root.querySelector('button');button.click();
const opened=button.getAttribute('aria-expanded')==='true'&&document.querySelector('#panel').getAttribute('aria-hidden')==='false';
button.click();const closed=button.getAttribute('aria-expanded')==='false';
document.querySelector('#result').textContent=opened&&closed&&!testErrors.length?'PASS: compiled runtime and lazy accordion':JSON.stringify({opened,closed,errors:testErrors});},50);
</script></body></html>`;
const server = createServer(async (request, response) => {
    try {
        const url = new URL(request.url, 'http://localhost');
        if (url.pathname === '/') {
            response.setHeader('Content-Type', 'text/html'); response.end(html); return;
        }
        if (url.pathname === '/favicon.ico') { response.writeHead(204).end(); return; }
        if (!url.pathname.startsWith(prefix)) throw new Error('unexpected request');
        const name = decodeURIComponent(url.pathname.slice(prefix.length));
        requests.push(name);
        const content = await readFile(safePath(assets, name));
        response.setHeader('Content-Type', name.endsWith('.js') ? 'text/javascript' : 'application/octet-stream');
        response.end(content);
    } catch (error) { missing.push(request.url); response.writeHead(404).end(error.message); }
});
await new Promise(resolve => server.listen(0, '127.0.0.1', resolve));
const profile = await mkdtemp(path.join(tmpdir(), 'rspack-browser-'));
try {
    const chrome = process.env.CHROME_BIN || (process.platform === 'darwin'
        ? '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome' : 'chromium');
    const child = spawn(chrome, ['--headless=new', '--no-first-run', '--no-default-browser-check', '--disable-gpu',
        `--user-data-dir=${profile}`, '--dump-dom', '--virtual-time-budget=6000',
        `http://127.0.0.1:${server.address().port}/`], { stdio: ['ignore', 'pipe', 'pipe'] });
    let output = ''; let errors = '';
    child.stdout.on('data', data => output += data);
    child.stderr.on('data', data => errors += data);
    const timeout = setTimeout(() => child.kill('SIGKILL'), 30000);
    const code = await new Promise((resolve, reject) => { child.once('error', reject); child.once('exit', resolve); });
    clearTimeout(timeout);
    assert.equal(code, 0, errors);
    assert.match(output, /<pre id="result">PASS: compiled runtime and lazy accordion<\/pre>/);
    assert.ok(requests.some(name => /^js\/chunks\/.+\.js$/.test(name)), 'a real async chunk was fetched');
    assert.deepEqual(missing, []);
    console.log(`Browser: compiled runtime, lazy chunks and accordion clicks OK (${requests.length} assets fetched).`);
} finally {
    await new Promise(resolve => server.close(resolve));
    await rm(profile, { recursive: true, force: true });
}
