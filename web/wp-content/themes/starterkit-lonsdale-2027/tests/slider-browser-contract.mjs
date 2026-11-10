import { spawn } from 'node:child_process';
import { readFileSync, mkdtempSync, rmSync } from 'node:fs';
import { tmpdir } from 'node:os';
import { fileURLToPath } from 'node:url';
import assert from 'node:assert/strict';

const directory = fileURLToPath(new URL('../../../../../', import.meta.url)).replace(/\/$/, '');
const profile = mkdtempSync(tmpdir() + '/slider-browser-');
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


    const js = readFileSync(directory + '/assets/components/slider/slider.js', 'utf8').replace('export { Slider };', '').replace('export default function slider(', 'function slider(');
    const evaluate = async expression => { const value = await send('Runtime.evaluate', { expression, returnByValue: true, awaitPromise: true }); if (value.exceptionDetails) throw new Error(JSON.stringify(value.exceptionDetails)); return value.result.value; };
    const {createRequire}=await import('node:module');const {compileString}=createRequire(directory+'/package.json')('sass');
    const css = compileString(readFileSync(directory + '/assets/styles/breakpoints.scss', 'utf8') + '\n' + readFileSync(directory + '/assets/components/slider/slider.scss', 'utf8')).css;
    const html = '<style>body{margin:0;--gap:0px}ul{padding:0;list-style:none}.slider{position:relative;width:600px;margin:40px auto}.item{height:200px}</style><style>'+css+'</style><div class="slider"><div class="slider-navigation"><button class="prev">Prev</button><button class="next">Next</button></div><div class="slider-wrapper"><ul class="slider-content" tabindex="0">'+[1,2,3,4].map(n=>'<li class="item"><a href="#s'+n+'">Slide '+n+'</a></li>').join('')+'</ul></div><div data-slider-pagination></div><div class="slider-timeline" data-slider-timeline><span class="slider-timeline-progress"></span></div><div data-slider-status></div></div>';
    await evaluate(js);
    const reset=async()=>{await evaluate('window.s?.remove();document.body.innerHTML='+JSON.stringify(html)+';window.s=new Slider(document.querySelector(".slider"));s.add();');await evaluate('new Promise(r=>requestAnimationFrame(()=>requestAnimationFrame(r)))');};
    const pause=ms=>evaluate(`new Promise(r=>setTimeout(r,${ms}))`);
    const gestureListeners = async () => {
        const { result } = await send('Runtime.evaluate', { expression: 'window' });
        const { listeners } = await send('DOMDebugger.getEventListeners', { objectId: result.objectId });
        return listeners.filter(listener => ['pointermove', 'pointerup', 'pointercancel', 'blur'].includes(listener.type)).length;
    };
    await reset();
    // Native smooth scroll changes the selected index before the destination is reached.
    await evaluate(`document.querySelector('.next').click()`);await pause(80);
    await evaluate(`document.querySelector('.next').click()`);await pause(1000);
    assert.ok(Math.abs(await evaluate(`document.querySelector('.slider-content').scrollLeft`)-1200)<2,'Two rapid next clicks must reach slide 3');
    await reset();
    assert.equal(await gestureListeners(),0,'Idle sliders have no global gesture listeners');
    const pos=await evaluate(`(()=>{const b=document.querySelector('.slider-content').getBoundingClientRect();return {x:b.left+500,y:b.top+100,bottom:b.bottom+50}})()`);
    await send('Input.dispatchMouseEvent',{type:'mousePressed',button:'left',buttons:1,x:pos.x,y:pos.y,clickCount:1});
    assert.equal(await gestureListeners(),4,'Gesture listeners exist only after pointerdown');
    await send('Input.dispatchMouseEvent',{type:'mouseMoved',button:'left',buttons:1,x:pos.x,y:pos.bottom});
    await send('Input.dispatchMouseEvent',{type:'mouseReleased',button:'left',buttons:0,x:pos.x,y:pos.bottom,clickCount:1});
    assert.equal(await evaluate(`getComputedStyle(document.querySelector('.slider-content')).scrollSnapType`),'x mandatory','Releasing outside must restore native snap');
    assert.equal(await gestureListeners(),0,'Releasing outside removes the gesture listeners');
    await reset();
    await send('Input.dispatchMouseEvent',{type:'mousePressed',button:'left',buttons:1,x:pos.x,y:pos.y,clickCount:1});
    await send('Input.dispatchMouseEvent',{type:'mouseMoved',button:'left',buttons:1,x:pos.x-80,y:pos.y});
    const cancelled=await evaluate(`(()=>{const c=document.querySelector('.slider-content');c.dispatchEvent(new PointerEvent('pointercancel',{bubbles:true,pointerId:1,pointerType:'mouse'}));return c.querySelector('a').dispatchEvent(new MouseEvent('click',{bubbles:true,cancelable:true,detail:0}));})()`);
    assert.equal(cancelled,true,'Cancelled drag must preserve keyboard link activation');
    assert.equal(await gestureListeners(),0,'Cancellation also removes gesture listeners');
    await send('Input.dispatchMouseEvent',{type:'mouseReleased',button:'left',buttons:0,x:pos.x-80,y:pos.y,clickCount:1});
    await reset();
    await evaluate(`window.mutations=0;window.mo=new MutationObserver(r=>{window.mutations+=r.length});mo.observe(document.querySelector('[data-slider-pagination]'),{subtree:true,attributes:true});`);
    for(let i=1;i<=10;i++){await evaluate(`document.querySelector('.slider-content').dispatchEvent(new Event('scroll'));new Promise(r=>requestAnimationFrame(()=>requestAnimationFrame(r)))`);}
    assert.equal(await evaluate('mutations'),0,'A stable page must not rewrite pagination on scroll');
    await evaluate('mo.disconnect();s.remove()');await pause(100);
    const removedPosition=await evaluate(`document.querySelector('.slider-content').scrollLeft`);
    await evaluate('s.slideTo(2)');await pause(1000);
    assert.equal(await evaluate(`document.querySelector('.slider-content').scrollLeft`),removedPosition,'slideTo must do nothing after remove');
    await evaluate('s.remove()');



    await reset();
    await send('Input.dispatchMouseEvent',{type:'mousePressed',button:'left',buttons:1,x:pos.x,y:pos.y,clickCount:1});
    await send('Input.dispatchMouseEvent',{type:'mouseMoved',button:'left',buttons:1,x:pos.x-50,y:pos.y});
    await evaluate('s.remove()');
    assert.equal(await gestureListeners(),0,'Removing a slider during a drag removes its global listeners');
    await send('Input.dispatchMouseEvent',{type:'mouseReleased',button:'left',buttons:0,x:pos.x-50,y:pos.y,clickCount:1});

    await reset();
    await evaluate(`(() => {
        const content = document.querySelector('.slider-content');
        window.dimensionReads = 0;
        for (const name of ['scrollWidth', 'clientWidth']) {
            const get = Object.getOwnPropertyDescriptor(Element.prototype, name).get;
            Object.defineProperty(content, name, { configurable: true, get() { dimensionReads++; return get.call(this); } });
        }
        window.timelineWrites = 0;
        window.timelineObserver = new MutationObserver(records => { timelineWrites += records.length; });
        timelineObserver.observe(document.querySelector('[data-slider-timeline]'), { attributes: true, attributeFilter: ['style'] });
        window.frameCount = 0;
        window.countFrames = true;
        const count = () => { if (countFrames) { frameCount++; requestAnimationFrame(count); } };
        requestAnimationFrame(count);
        s.slideTo(2);
    })()`);
    await pause(1000);
    const metrics = await evaluate(`countFrames=false;timelineObserver.disconnect();({dimensions:dimensionReads,writes:timelineWrites,frames:frameCount})`);
    assert.equal(metrics.dimensions,0,'Animation uses the measured scroll range instead of rereading dimensions');
    assert.ok(metrics.writes > 0 && metrics.writes <= metrics.frames + 2,'The timeline is written at most once per animation frame');
    await evaluate(`window.stableWrites=0;window.stableObserver=new MutationObserver(records=>{stableWrites+=records.length});stableObserver.observe(document.querySelector('[data-slider-timeline]'),{attributes:true});`);
    for(let i=0;i<3;i++) await evaluate(`document.querySelector('.slider-content').dispatchEvent(new Event('scroll'));new Promise(r=>requestAnimationFrame(()=>requestAnimationFrame(r)))`);
    assert.equal(await evaluate('stableWrites'),0,'An unchanged progress value does not rewrite timeline styles');
    await evaluate('stableObserver.disconnect()');
    console.log('Slider animation metrics:',metrics);

    // Normal drag still lands on slide 2; a duplicate/untracked release does nothing.
    await reset();
    await send('Input.dispatchMouseEvent',{type:'mousePressed',button:'left',buttons:1,x:pos.x,y:pos.y,clickCount:1});
    await send('Input.dispatchMouseEvent',{type:'mouseMoved',button:'left',buttons:1,x:pos.x-50,y:pos.y});
    await send('Input.dispatchMouseEvent',{type:'mouseMoved',button:'left',buttons:1,x:pos.x-580,y:pos.y});
    await send('Input.dispatchMouseEvent',{type:'mouseReleased',button:'left',buttons:0,x:pos.x-580,y:pos.y,clickCount:1});
    await pause(1000);
    assert.ok(Math.abs(await evaluate(`document.querySelector('.slider-content').scrollLeft`)-600)<2,'Normal drag lands on slide 2');
    await evaluate(`document.querySelector('.slider-content').dispatchEvent(new PointerEvent('pointerup',{bubbles:true,pointerId:22,pointerType:'touch'}));`);await pause(100);
    assert.ok(Math.abs(await evaluate(`document.querySelector('.slider-content').scrollLeft`)-600)<2,'Untracked touch release must not move the slider');
    await reset();
    await send('Emulation.setEmulatedMedia',{features:[{name:'prefers-reduced-motion',value:'reduce'}]});
    await evaluate('s.slideTo(0);s.add();s.add()');
    assert.equal(await evaluate(`document.querySelectorAll('.slider-dot').length`),4,'Repeated add is idempotent');
    await evaluate(`document.querySelector('.slider-content').dispatchEvent(new KeyboardEvent('keydown',{bubbles:true,key:'ArrowRight'}))`);
    assert.ok(Math.abs(await evaluate(`document.querySelector('.slider-content').scrollLeft`)-600)<2,'Keyboard right and reduced motion');
    await evaluate('s.remove();s.add()');
    assert.equal(await evaluate(`document.querySelectorAll('.slider-dot').length`),4,'Remount rebuilds one pagination');
    await evaluate(`document.querySelector('.slider').style.setProperty('--nb','2')`);await pause(100);
    assert.equal(await evaluate(`document.querySelectorAll('.slider-dot').length`),3,'Clamped slide positions are deduplicated');
    await evaluate('s.slideTo(999)');
    assert.ok(Math.abs(await evaluate(`document.querySelector('.slider-content').scrollLeft`)-600)<2,'Resizing items refreshes the cached range');
    await evaluate(`document.querySelector('.slider').style.setProperty('--nb','4')`);await pause(100);
    assert.equal(await evaluate(`document.querySelector('.slider').classList.contains('disable')`),true,'No overflow disables controls');
    await evaluate('s.remove()');
    console.log('Slider: functional regressions, temporary gesture listeners, cached dimensions, timeline writes and responsive checks OK.');
} finally {
    clearTimeout(timeout);
    socket?.close();
    child.kill('SIGTERM');
    if (child.exitCode === null && child.signalCode === null) await new Promise(resolve => child.once('exit', resolve));
    rmSync(profile, { recursive: true, force: true });
}
