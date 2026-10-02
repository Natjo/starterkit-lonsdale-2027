import { spawn } from 'node:child_process';
import { readFileSync, mkdtempSync, rmSync } from 'node:fs';
import { tmpdir } from 'node:os';
import { fileURLToPath } from 'node:url';
import assert from 'node:assert/strict';

const directory = fileURLToPath(new URL('../../../../../', import.meta.url)).replace(/\/$/, '');
const profile = mkdtempSync(tmpdir() + '/header-nav-complex-browser-');
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
    const js = readFileSync(directory + '/assets/common/header-nav-complex/header-nav-complex.js', 'utf8').replace('export default function HeaderNav()', 'function HeaderNav()');
    const css = readFileSync(directory + '/web/wp-content/themes/starterkit-lonsdale-2027/assets/css/critical.css', 'utf8');
    const html = '<style>' + css + '</style><header id="header-nav-complex"><button class="btn-nav">Menu</button><nav id="nav"><ul class="header-nav-menu"><li id="p"><a href="#p">Parent</a><div class="wrapper-level"><ul class="sub-menu"><li id="c"><a href="#c">Enfant</a><div class="wrapper-level"><ul class="sub-menu"><li><a href="#g">Petit enfant</a></li></ul></div></li></ul></div></li><li><a href="#other">Autre</a></li></ul></nav></header><button id="outside">Extérieur</button>';
    const evaluate = async expression => { const value = await send('Runtime.evaluate', { expression, returnByValue: true, awaitPromise: true }); if (value.exceptionDetails) throw new Error(JSON.stringify(value.exceptionDetails)); return value.result.value; };
    await evaluate('document.body.innerHTML=' + JSON.stringify(html));
    await evaluate(`const viewport = document.createElement('meta'); viewport.name = 'viewport'; viewport.content = 'width=device-width, initial-scale=1'; document.head.append(viewport);`);
    await evaluate(js + '\nHeaderNav(); HeaderNav();');
    assert.equal(await evaluate('document.querySelectorAll(".header-submenu-toggle").length'), 2);
    assert.equal(await evaluate('document.querySelector("#p>.wrapper-level").inert'), true);
    assert.equal(await evaluate(`document.querySelector('.btn-nav').getAttribute('aria-expanded')`), 'false');
    await evaluate(`document.querySelector('.btn-nav').click(); document.querySelector('#p>a').click();`);
    assert.equal(await evaluate(`document.querySelector('#header-nav-complex').classList.contains('is-nav-animating')`), false);
    await send('Input.dispatchKeyEvent', { type: 'keyDown', key: 'Tab', code: 'Tab', windowsVirtualKeyCode: 9 });
    await send('Input.dispatchKeyEvent', { type: 'keyUp', key: 'Tab', code: 'Tab', windowsVirtualKeyCode: 9 });
    const desktop = await evaluate(`(() => { const p=document.querySelector('#p'), w=p.querySelector('.wrapper-level'), s=w.firstElementChild; p.querySelector('a').focus(); const c=document.querySelector('#c'); c.querySelector('a').focus(); const n=c.querySelector('.wrapper-level'); return {open:p.classList.contains('is-submenu-open'), nestedOpen:c.classList.contains('is-submenu-open'), overflow:getComputedStyle(s).overflow, wrapperTransition:getComputedStyle(w).transitionDuration, subTransition:getComputedStyle(s).transitionDuration, height:w.getBoundingClientRect().height, nestedHeight:n.getBoundingClientRect().height, gap:w.getBoundingClientRect().top-p.getBoundingClientRect().bottom}; })()`);
    assert.equal(desktop.open, true); assert.equal(desktop.nestedOpen, true); assert.equal(desktop.overflow, 'visible'); assert.equal(desktop.wrapperTransition, '0s'); assert.equal(desktop.subTransition, '0s'); assert.ok(desktop.height > 0); assert.ok(desktop.nestedHeight > 0); assert.equal(desktop.gap, 0);
    await evaluate(`document.querySelector('#c>a').dispatchEvent(new KeyboardEvent('keydown',{key:'Escape',bubbles:true}));`);
    assert.equal(await evaluate(`document.querySelector('#c').classList.contains('is-submenu-open')`), false);
    // Focusing the disclosure must not open it before its click, including wide touch screens.
    await evaluate(`document.querySelector('#outside').focus(); document.querySelector('#p>button').focus();`);
    assert.equal(await evaluate(`document.querySelector('#p').classList.contains('is-submenu-open')`), false);
    await evaluate(`document.querySelector('#p>button').click();`);
    assert.equal(await evaluate(`document.querySelector('#p').classList.contains('is-submenu-open')`), true);
    await evaluate(`document.querySelector('#p>button').click();`);
    assert.equal(await evaluate(`getComputedStyle(document.querySelector('#p>.wrapper-level')).getPropertyValue('--submenu-height').trim()`), '0px');
    await send('Emulation.setEmulatedMedia', { features: [{ name: 'prefers-reduced-motion', value: 'no-preference' }, { name: 'hover', value: 'hover' }] });
    await evaluate(`document.querySelector('#outside').focus(); document.querySelector('#p>a').focus();`);
    assert.equal(await evaluate(`getComputedStyle(document.querySelector('#p>.wrapper-level>.sub-menu')).overflow`), 'hidden');
    await evaluate(`new Promise(resolve=>setTimeout(resolve,400))`);
    assert.equal(await evaluate(`getComputedStyle(document.querySelector('#p>.wrapper-level>.sub-menu')).overflow`), 'visible');
    assert.equal(await evaluate(`document.querySelector('#p>.wrapper-level').getBoundingClientRect().height`), 60);
    await send('Emulation.setDeviceMetricsOverride', { width: 390, height: 844, deviceScaleFactor: 1, mobile: true });
    await evaluate(`new Promise(resolve=>setTimeout(resolve,100))`);
    await evaluate(`document.querySelector('.btn-nav').click(); document.querySelector('#p>button').focus(); document.querySelector('#p>button').click();`);
    assert.equal(await evaluate(`getComputedStyle(document.querySelector('#p>.wrapper-level')).display`), 'block');
    assert.equal(await evaluate(`document.querySelector('.btn-nav').getAttribute('aria-expanded')`), 'true');
    assert.equal(await evaluate(`document.querySelector('#nav').hasAttribute('data-lenis-prevent')`), true);
    await evaluate(`document.querySelector('#header-nav-complex').dispatchEvent(new KeyboardEvent('keydown',{key:'Escape',bubbles:true}));`);
    assert.equal(await evaluate(`document.querySelector('.btn-nav').getAttribute('aria-expanded')`), 'false');
    assert.equal(await evaluate(`document.activeElement === document.querySelector('.btn-nav')`), true);
    assert.equal(await evaluate(`document.querySelector('#nav').hasAttribute('data-lenis-prevent')`), false);
    await evaluate(`new Promise(resolve=>setTimeout(resolve,350))`);
    assert.equal(await evaluate(`document.querySelector('#header-nav-complex').classList.contains('is-nav-animating')`), false);
    await evaluate(`document.querySelector('.btn-nav').click(); document.querySelector('#p>a').click();`);
    assert.equal(await evaluate(`document.querySelector('.btn-nav').getAttribute('aria-expanded')`), 'false');
    await evaluate(`document.querySelector('.btn-nav').click();`);
    await send('Emulation.setDeviceMetricsOverride', { width: 1280, height: 900, deviceScaleFactor: 1, mobile: false });
    await evaluate(`new Promise(resolve=>setTimeout(resolve,100))`);
    assert.equal(await evaluate(`document.querySelector('.btn-nav').getAttribute('aria-expanded')`), 'false');
    assert.equal(await evaluate(`document.querySelector('#header-nav-complex').classList.contains('is-nav-animating')`), false);
    assert.equal(await evaluate(`document.querySelector('#nav').hasAttribute('data-lenis-prevent')`), false);
    console.log('Header complex browser: OK (desktop, nested levels, Escape, touch disclosure, mobile, reduced motion, idempotence)');
    console.log(JSON.stringify(desktop));
    await call('Browser.close');
} finally {
    clearTimeout(timeout);
    socket?.close();
    child.kill('SIGTERM');
    if (child.exitCode === null && child.signalCode === null) await new Promise(resolve => child.once('exit', resolve));
    rmSync(profile, { recursive: true, force: true });
}
