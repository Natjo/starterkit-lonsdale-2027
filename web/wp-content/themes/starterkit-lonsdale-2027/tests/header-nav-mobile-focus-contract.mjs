import { spawn } from 'node:child_process';
import { readFileSync, mkdtempSync, rmSync } from 'node:fs';
import { tmpdir } from 'node:os';
import { fileURLToPath } from 'node:url';
import assert from 'node:assert/strict';

const directory = fileURLToPath(new URL('../../../../../', import.meta.url)).replace(/\/$/, '');
const profile = mkdtempSync(tmpdir() + '/header-nav-mobile-focus-');
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
    const css = readFileSync(directory + '/web/wp-content/themes/starterkit-lonsdale-2027/assets/css/critical.css', 'utf8');
    const evaluate = async expression => { const value = await send('Runtime.evaluate', { expression, returnByValue: true, awaitPromise: true }); if (value.exceptionDetails) throw new Error(JSON.stringify(value.exceptionDetails)); return value.result.value; };
    await evaluate(`const meta=document.createElement('meta');meta.name='viewport';meta.content='width=device-width, initial-scale=1';document.head.append(meta);`);
    const key = async (key, shift = false) => {
        await send('Input.dispatchKeyEvent', {type:'keyDown',key,code:key,windowsVirtualKeyCode:key === 'Tab' ? 9 : 27,modifiers:shift ? 8 : 0});
        await send('Input.dispatchKeyEvent', {type:'keyUp',key,code:key,windowsVirtualKeyCode:key === 'Tab' ? 9 : 27,modifiers:shift ? 8 : 0});
    };
    for (const name of ['header-nav', 'header-nav-subnav', 'header-nav-dropdown', 'header-nav-cta', 'header-nav-centered', 'header-nav-minimal', 'header-nav-search', 'header-nav-topbar', 'header-nav-commerce']) {
        await send('Emulation.setDeviceMetricsOverride', {width:390,height:844,deviceScaleFactor:1,mobile:true});
        const html='<style>'+css+'</style><header id="'+name+'"><button class="btn-nav" id="burger">Menu</button><nav id="nav"><ul class="nav-menu header-nav-menu"><li><a id="first" href="#first">Premier</a></li><li><button disabled>Disabled</button><div inert><a href="#hidden">Hidden</a></div><a style="display:none" href="#hidden2">Hidden</a><button id="last">Dernier</button></li></ul></nav></header><button id="outside">Outside</button>';
        await evaluate('document.body.innerHTML='+JSON.stringify(html));
        const source=readFileSync(directory+'/assets/common/'+name+'/'+name+'.js','utf8');
        const end=source.includes('function HeaderScroll(root') ? source.indexOf('function HeaderScroll(root') : source.indexOf('export default function HeaderNav()');
        const navigation=source.slice(source.indexOf('function Navigation(root)'),end);
        await evaluate('{ const MOBILE_MEDIA='+JSON.stringify(name === 'header-nav-minimal' ? '(min-width: 0px)' : '(max-width: 767px)')+';'+navigation+'Navigation(document.getElementById('+JSON.stringify(name)+')); }');
        assert.equal(await evaluate('document.querySelector("#nav").inert'),true,name);
        await send('Emulation.setEmulatedMedia', { features: [{ name: 'prefers-reduced-motion', value: 'no-preference' }] });
        await evaluate('document.querySelector("#burger").focus()');
        await send('Page.bringToFront');
        await send('Input.dispatchKeyEvent', {type:'keyDown',key:'Enter',code:'Enter',windowsVirtualKeyCode:13,text:'\r'});
        await send('Input.dispatchKeyEvent', {type:'keyUp',key:'Enter',code:'Enter',windowsVirtualKeyCode:13});
        await evaluate('new Promise(resolve=>setTimeout(resolve,100))');
        assert.equal(await evaluate('document.activeElement.id'),'first',name+' first focus');
        await key('Tab',true);
        assert.equal(await evaluate('document.activeElement.id'),'last',name+' reverse wrap');
        await key('Tab');
        assert.equal(await evaluate('document.activeElement.id'),'first',name+' forward wrap');
        await key('Tab');
        assert.equal(await evaluate('document.activeElement.id'),'last',name+' skip hidden/disabled');
        await evaluate('document.querySelector("#outside").focus()');
        assert.equal(await evaluate('document.activeElement.id'),'first',name+' containment');
        await key('Escape');
        assert.equal(await evaluate('document.activeElement.id'),'burger',name+' Escape return');
        assert.equal(await evaluate('document.querySelector("#nav").inert'),true,name+' closed');
        // Empty navigation still traps focus and permits Escape.
        await evaluate('document.querySelector("#nav").innerHTML=""; document.querySelector("#burger").click()');
        await evaluate('new Promise(resolve=>setTimeout(resolve,100))');
        assert.equal(await evaluate('document.activeElement.id'),'nav',name+' empty fallback');
        await key('Tab');
        assert.equal(await evaluate('document.activeElement.id'),'nav',name+' empty trap');
        await key('Escape');
        await send('Emulation.setDeviceMetricsOverride',{width:1280,height:900,deviceScaleFactor:1,mobile:false});
        await evaluate('new Promise(resolve=>setTimeout(resolve,100))');
        assert.equal(await evaluate('document.querySelector("#nav").inert'),name === 'header-nav-minimal',name+' desktop');
        if (name === 'header-nav-minimal') {
            assert.notEqual(await evaluate('getComputedStyle(document.querySelector("#burger")).display'),'none');
            await evaluate('document.querySelector("#nav").innerHTML="<a id=desktop-link href=#test>Desktop</a>";document.querySelector("#burger").click()');
            await evaluate('new Promise(resolve=>setTimeout(resolve,100))');
            assert.equal(await evaluate('document.activeElement.id'),'desktop-link');
            await key('Escape');
            assert.equal(await evaluate('document.activeElement.id'),'burger');
        }
        if (name === 'header-nav-centered') {
            await evaluate(`(() => { const logo=document.createElement('a');logo.className='header-nav-logo';logo.textContent='Logo';document.querySelector('header').prepend(logo);const list=document.createElement('ul');list.className='header-nav-menu header-nav-menu-secondary';list.innerHTML='<li>Second menu</li>';document.querySelector('#nav').append(list); })()`);
            assert.ok(await evaluate('(() => { const logo=document.querySelector(".header-nav-logo").getBoundingClientRect(); return Math.abs(logo.left+logo.width/2-window.innerWidth/2)<1; })()'), 'centered logo');
        }
        console.log(name+': mobile focus trap OK');
    }

} finally {
    clearTimeout(timeout);
    socket?.close();
    child.kill('SIGTERM');
    if (child.exitCode === null && child.signalCode === null) await new Promise(resolve => child.once('exit', resolve));
    rmSync(profile, { recursive: true, force: true });
}
