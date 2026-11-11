import { spawn } from 'node:child_process';
import { readFileSync, mkdtempSync, rmSync } from 'node:fs';
import { tmpdir } from 'node:os';
import { fileURLToPath } from 'node:url';
import assert from 'node:assert/strict';

const directory = fileURLToPath(new URL('../../../../../', import.meta.url)).replace(/\/$/, '');
const profile = mkdtempSync(tmpdir() + '/header-nav-dropdown-browser-');
const child = spawn(process.env.CHROME_BIN || '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome', ['--headless', '--no-sandbox', '--disable-gpu', '--no-first-run', '--disable-background-networking', '--remote-debugging-port=0', '--user-data-dir=' + profile]);
const timeout = setTimeout(() => { child.kill('SIGKILL'); process.exit(1); }, 45000);
let socket;
try {
    const endpoint = await new Promise((resolve, reject) => {
        let stderr = '';
        child.stderr.on('data', (chunk) => {
            stderr += chunk;
            const match = stderr.match(/DevTools listening on (ws:\/\/[^\s]+)/);
            if (match) resolve(match[1]);
        });
        child.on('error', reject);
        child.on('exit', (code) => reject(new Error('Chrome exited: ' + code)));
    });
    socket = new WebSocket(endpoint);
    await new Promise(resolve => socket.addEventListener('open', resolve, { once: true }));
    const waiting = new Map();
    let id = 0;
    socket.addEventListener('message', ({ data }) => {
        const response = JSON.parse(data);
        if (response.id) { const callbacks = waiting.get(response.id); if (!callbacks) return; waiting.delete(response.id); response.error ? callbacks.reject(response.error) : callbacks.resolve(response.result); }
    });
    const call = (method, params = {}, sessionId) => new Promise((resolve, reject) => { const next = ++id; waiting.set(next, { resolve, reject }); socket.send(JSON.stringify({ id: next, method, params, sessionId })); });
    const { targetId } = await call('Target.createTarget', { url: 'about:blank' });
    const { sessionId } = await call('Target.attachToTarget', { targetId, flatten: true });
    const send = (method, params) => call(method, params, sessionId);
    await send('Emulation.setDeviceMetricsOverride', { width: 1280, height: 900, deviceScaleFactor: 1, mobile: false });
    await send('Emulation.setEmulatedMedia', { features: [{ name: 'prefers-reduced-motion', value: 'reduce' }, { name: 'hover', value: 'hover' }] });
    const js = readFileSync(directory + '/assets/common/header-nav-dropdown/header-nav-dropdown.js', 'utf8').replace('export default function HeaderNav(root)', 'function HeaderNav(root)');
    const css = readFileSync(directory + '/web/wp-content/themes/starterkit-lonsdale-2027/assets/css/critical.css', 'utf8');
    const evaluate = async expression => { const value = await send('Runtime.evaluate', { expression, returnByValue: true, awaitPromise: true }); if (value.exceptionDetails) throw new Error(JSON.stringify(value.exceptionDetails)); return value.result.value; };
    const html = '<style>' + css + '</style><header id="header-nav-dropdown"><button class="btn-nav" aria-expanded="false">Menu</button><nav id="nav"><ul class="nav-menu"><li id="p"><a href="#p">Parent</a><div class="level1"><div class="wrapper-sub-menu"><ul class="sub-menu"><li><a href="#child">Enfant</a></li></ul></div></div></li></ul></nav></header><button id="outside">Outside</button>';
    await evaluate('document.body.innerHTML=' + JSON.stringify(html));
    await evaluate(`const viewport = document.createElement('meta'); viewport.name = 'viewport'; viewport.content = 'width=device-width, initial-scale=1'; document.head.append(viewport);`);
    await evaluate(js + '\nHeaderNav(); HeaderNav();');
    assert.equal(await evaluate('document.querySelectorAll(".submenu-toggle").length'), 1);
    assert.equal(await evaluate('document.querySelector(".level1").style.getPropertyValue("--height")'), '');
    assert.equal(await evaluate('document.querySelector(".level1").getBoundingClientRect().height'), 0);
    await send('Emulation.setEmulatedMedia', { features: [{ name: 'prefers-reduced-motion', value: 'no-preference' }] });
    await evaluate('document.querySelector("#outside").focus(); document.querySelector(".level1").getBoundingClientRect(); document.querySelector("#p>a").focus()');
    await evaluate('new Promise(resolve=>setTimeout(resolve,80))');
    const openingHeight = await evaluate('document.querySelector(".level1").getBoundingClientRect().height');
    await evaluate('new Promise(resolve=>setTimeout(resolve,300))');
    const fullHeight = await evaluate('document.querySelector(".level1").getBoundingClientRect().height');
    assert.ok(openingHeight > 0 && openingHeight < fullHeight, 'Opening height is animated');
    await evaluate('document.querySelector("#outside").focus()');
    await evaluate('new Promise(resolve=>setTimeout(resolve,80))');
    const closingHeight = await evaluate('document.querySelector(".level1").getBoundingClientRect().height');
    assert.ok(closingHeight > 0 && closingHeight < fullHeight, 'Closing height is animated');
    await evaluate('new Promise(resolve=>setTimeout(resolve,300))');
    await send('Emulation.setEmulatedMedia', { features: [{ name: 'prefers-reduced-motion', value: 'reduce' }] });
    assert.equal(await evaluate('document.querySelector(".level1").getBoundingClientRect().height'), 0);
    assert.equal(await evaluate('document.querySelector(".level1").inert'), true);
    await evaluate('document.querySelector("#p>a").focus()');
    assert.equal(await evaluate('document.querySelector("#p").classList.contains("is-submenu-open")'), true);
    assert.equal(await evaluate('document.querySelector(".level1").inert'), false);
    assert.ok(await evaluate('document.querySelector(".level1").getBoundingClientRect().height') > 0);
    const previousHeight = await evaluate('document.querySelector(".level1").getBoundingClientRect().height');
    await evaluate('document.querySelector(".sub-menu>a, .sub-menu li>a").innerHTML += "<br>Une autre ligne"');
    assert.ok(await evaluate('document.querySelector(".level1").getBoundingClientRect().height') > previousHeight);
    assert.equal(await evaluate('document.querySelector("#p").classList.contains("is-submenu-revealed")'), false);
    await evaluate('document.querySelector("#p>a").dispatchEvent(new KeyboardEvent("keydown",{key:"Escape",bubbles:true}))');
    assert.equal(await evaluate('document.querySelector("#p").classList.contains("is-submenu-open")'), false);
    assert.equal(await evaluate('document.querySelector(".level1").getBoundingClientRect().height'), 0);
    await send('Emulation.setDeviceMetricsOverride', { width: 390, height: 844, deviceScaleFactor: 1, mobile: true });
    await evaluate('new Promise(resolve => setTimeout(resolve, 100))');
    await evaluate('document.querySelector(".btn-nav").click();document.querySelector(".submenu-toggle").click()');
    assert.equal(await evaluate('document.querySelector("#header-nav-dropdown").classList.contains("is-open")'), true);
    assert.equal(await evaluate('document.querySelector("#p").classList.contains("is-submenu-open")'), true);
    assert.equal(await evaluate('document.querySelector(".submenu-toggle").getAttribute("aria-expanded")'), 'true');
    await evaluate('document.querySelector("#outside").dispatchEvent(new PointerEvent("pointerdown",{bubbles:true}))');
    assert.equal(await evaluate('document.querySelector("#p").classList.contains("is-submenu-open")'), false);
    await evaluate('const dispose=HeaderNav();dispose();dispose();document.querySelector("#outside").focus()');
    assert.equal(await evaluate('document.activeElement.id'),'outside','Cleanup releases the focus trap');
    assert.equal(await evaluate('document.querySelectorAll(".submenu-toggle").length'),0,'Cleanup removes generated controls');
    await evaluate('HeaderNav();document.querySelector(".btn-nav").click()');
    await evaluate('new Promise(resolve=>setTimeout(resolve,100))');
    assert.equal(await evaluate('document.querySelectorAll(".submenu-toggle").length'),1,'Reinitialization adds one set of controls');
    assert.equal(await evaluate('document.querySelector("#nav").contains(document.activeElement)'),true,'Reinitialized mobile focus works');
    await evaluate('HeaderNav()()');
    console.log('Header dropdown: OK (natural heights, keyboard, Escape, inert, mobile, outside click, idempotence)');

} finally {
    clearTimeout(timeout);
    socket?.close();
    child.kill('SIGTERM');
    if (child.exitCode === null && child.signalCode === null) await new Promise(resolve => child.once('exit', resolve));
    rmSync(profile, { recursive: true, force: true });
}
