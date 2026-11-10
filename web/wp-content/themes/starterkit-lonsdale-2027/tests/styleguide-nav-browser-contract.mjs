import { spawn, execFileSync } from 'node:child_process';
import { readFileSync, mkdtempSync, rmSync } from 'node:fs';
import { tmpdir } from 'node:os';
import { fileURLToPath } from 'node:url';
import assert from 'node:assert/strict';
import { createServer } from 'node:http';

const directory = fileURLToPath(new URL('../../../../../', import.meta.url)).replace(/\/$/, '');
let fixtureHtml = '<!doctype html><html><body></body></html>';
let generation = 0;
const fixtureServer = createServer((request, response) => {
    response.writeHead(200, { 'Content-Type': 'text/html; charset=utf-8', 'Cache-Control': 'no-store' });
    response.end(fixtureHtml + `<script>window.fixtureGeneration=${++generation};</script>`);
});
await new Promise(resolve => fixtureServer.listen(0, '127.0.0.1', resolve));
const fixtureOrigin = `http://127.0.0.1:${fixtureServer.address().port}`;
const profile = mkdtempSync(tmpdir() + '/styleguide-nav-browser-');
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
    const { targetId } = await call('Target.createTarget', { url: fixtureOrigin });
    const { sessionId } = await call('Target.attachToTarget', { targetId, flatten: true });
    const send = (method, params) => call(method, params, sessionId);
    await send('Emulation.setDeviceMetricsOverride', { width: 1280, height: 900, deviceScaleFactor: 1, mobile: false });


    const js = readFileSync(directory + '/assets/pages/styleguide/styleguide.js', 'utf8').replace('export default function initStyleguide(', 'function initStyleguide(');
    const css = readFileSync(directory + '/web/wp-content/themes/starterkit-lonsdale-2027/inc/front/styleguide/styleguide.css', 'utf8');
    const evaluate = async expression => { const value = await send('Runtime.evaluate', { expression, returnByValue: true, awaitPromise: true }); if (value.exceptionDetails) throw new Error(JSON.stringify(value.exceptionDetails)); return value.result.value; };
    const wait = async expression => {
        for (let attempt=0;attempt<100;attempt++) {
            try { if (await evaluate(expression)) return; } catch { /* Navigation replaces the context. */ }
            await new Promise(resolve=>setTimeout(resolve,30));
        }
        throw new Error('Timed out: '+expression);
    };
    await wait('window.fixtureGeneration > 0 && document.readyState === "complete"');
    await evaluate('document.body.innerHTML=' + JSON.stringify('<style>'+css+'</style><main class="styleguide"><nav class="styleguide-nav" style="width:260px;position:static"><ul>'+Array.from({length:16},(_,i)=>'<li><a href="#section-'+i+'">Component '+i+'</a></li>').join('')+'</ul></nav>'+Array.from({length:16},(_,i)=>'<section id="section-'+i+'" style="height:200px">Section '+i+'</section>').join('')+'</main>'));
    await evaluate(js + '\nwindow.cleanup = initStyleguide(document.querySelector("main"));');
    await evaluate(`new Promise(resolve=>requestAnimationFrame(()=>requestAnimationFrame(resolve)))`);
    assert.equal(await evaluate(`document.querySelector('.styleguide-nav ul').classList.contains('has-overflow-start')`),false);
    assert.equal(await evaluate(`document.querySelector('.styleguide-nav ul').classList.contains('has-overflow-end')`),true);
    const box=await evaluate(`(()=>{const b=document.querySelector('.styleguide-nav ul').getBoundingClientRect();return {x:b.left+200,y:b.top+15}})()`);
    assert.equal(await evaluate(`getComputedStyle(document.querySelector('.styleguide-nav ul')).scrollbarWidth`),'none');
    await send('Input.dispatchMouseEvent',{type:'mousePressed',button:'left',buttons:1,x:box.x,y:box.y,clickCount:1});
    await send('Input.dispatchMouseEvent',{type:'mouseMoved',button:'left',buttons:1,x:box.x-80,y:box.y});
    await send('Input.dispatchMouseEvent',{type:'mouseReleased',button:'left',buttons:0,x:box.x-80,y:box.y,clickCount:1});
    assert.ok(await evaluate(`document.querySelector('.styleguide-nav ul').scrollLeft >= 70`),'Mouse drag scrolls the navigation');
    assert.equal(await evaluate('location.hash'),'','Drag must not activate an anchor');
    await evaluate(`document.querySelector('.styleguide-nav a').click()`);
    assert.equal(await evaluate('location.hash'),'#section-0','Keyboard-style click still follows the anchor');
    await evaluate(`document.querySelector('.styleguide-nav ul').scrollLeft=99999; new Promise(resolve=>setTimeout(resolve,100))`);
    assert.equal(await evaluate(`document.querySelector('.styleguide-nav ul').classList.contains('has-overflow-end')`),false,'End fade disappears at the last link');
    assert.equal(await evaluate(`document.querySelector('.styleguide-nav ul').classList.contains('has-overflow-start')`),true);
    await evaluate('cleanup()');
    assert.equal(await evaluate(`document.querySelector('.styleguide-nav ul').classList.contains('is-dragging')`),false);

    await evaluate(`(() => {
        const nav=document.querySelector('.styleguide-nav');
        nav.classList.add('styleguide-nav--search');nav.style.width='800px';
        nav.insertAdjacentHTML('afterbegin','<input type="search" data-styleguide-search class="styleguide-search" aria-label="Rechercher un composant">');
        nav.insertAdjacentHTML('afterend','<p data-styleguide-empty role="status" hidden>Aucun composant trouvé.</p>');
        window.cleanup=initStyleguide(document.querySelector('main'));
    })()`);
    await evaluate(`const search=document.querySelector('[data-styleguide-search]');search.value='COMPÓnent 12';search.dispatchEvent(new Event('input',{bubbles:true}));`);
    assert.equal(await evaluate(`document.querySelectorAll('.styleguide-nav li:not([hidden])').length`),1,'Search filters navigation ignoring case and accents');
    assert.equal(await evaluate(`document.querySelectorAll('main>section:not([hidden])').length`),1,'Search also filters the demos');
    assert.equal(await evaluate(`document.getElementById('section-12').hidden`),false);
    await evaluate(`search.value='absent';search.dispatchEvent(new Event('input',{bubbles:true}));`);
    assert.equal(await evaluate(`document.querySelector('[data-styleguide-empty]').hidden`),false,'Empty results are announced');
    await evaluate(`search.value='';search.dispatchEvent(new Event('input',{bubbles:true}));`);
    assert.equal(await evaluate(`document.querySelectorAll('main>section:not([hidden])').length`),16,'Clearing restores all demos');
    assert.equal(await evaluate(`document.querySelector('[data-styleguide-empty]').hidden`),true);
    await evaluate('cleanup()');

    for (const prefix of ['card', 'strate', 'hero', 'style']) {
        await evaluate(`document.querySelector('main').innerHTML = '<nav class="styleguide-nav styleguide-nav--search"><input type="search" data-styleguide-search class="styleguide-search" aria-label="Rechercher un exemple"><ul><li><a href="#${prefix}-alpha">Alpha</a></li><li><a href="#${prefix}-beta">Beta</a></li></ul></nav><p data-styleguide-empty role="status" hidden>Aucun exemple trouvé.</p><div class="st-components"><section class="styleguide-section styleguide-section--full" id="${prefix}-alpha">Alpha</section><section class="styleguide-section styleguide-section--full" id="${prefix}-beta">Beta</section></div>';window.cleanup=initStyleguide(document.querySelector('main'));`);
        await evaluate(`(()=>{const input=document.querySelector('[data-styleguide-search]');input.value='beta';input.dispatchEvent(new Event('input',{bubbles:true}));})()`);
        assert.equal(await evaluate(`document.querySelectorAll('.styleguide-nav li:not([hidden])').length`),1,prefix+' navigation is filtered');
        assert.equal(await evaluate(`getComputedStyle(document.getElementById('${prefix}-alpha')).display`),'none',prefix+' nonmatching section is hidden');
        assert.notEqual(await evaluate(`getComputedStyle(document.getElementById('${prefix}-beta')).display`),'none',prefix+' matching section stays visible');
        await evaluate(`(()=>{const input=document.querySelector('[data-styleguide-search]');input.value='';input.dispatchEvent(new Event('input',{bubbles:true}));})()`);
        assert.equal(await evaluate(`document.querySelectorAll('.styleguide-section:not([hidden])').length`),2,prefix+' clearing restores all sections');
        await evaluate('cleanup()');
    }




    for (const [family,type] of [['heros','hero'],['strates','strate']]) {
        await evaluate(`document.body.style.margin='0';document.querySelector('main').classList.add('styleguide--${family}');document.querySelector('main').innerHTML='<div class="st-components"><section class="styleguide-section styleguide-section--full"><div class="styleguide-demo"><h2 class="styleguide-section-title">${type}-page</h2><div class="styleguide-${type}-preview"><div data-example style="height:140px">Example</div></div></div></section></div>';`);
        for (const width of [1280,390]) {
            await send('Emulation.setDeviceMetricsOverride',{width,height:900,deviceScaleFactor:1,mobile:false});
            const layout=await evaluate(`(()=>{const preview=document.querySelector('.styleguide-${type}-preview');const box=preview.querySelector('[data-example]').getBoundingClientRect();const style=getComputedStyle(document.querySelector('.styleguide-section'));return {left:box.left,width:box.width,viewport:document.documentElement.clientWidth,border:style.borderTopWidth,padding:style.paddingLeft,topBorder:getComputedStyle(preview).borderTopWidth};})()`);
            assert.ok(Math.abs(layout.left)<1 && Math.abs(layout.width-layout.viewport)<1,family+' span the viewport at '+width+'px');
            assert.equal(layout.border,'0px',family+' examples have no frame');
            assert.equal(layout.padding,'0px',family+' examples have no extra padding');
            assert.equal(layout.topBorder,'1px',family+' examples keep the top border');
        }
        await evaluate(`document.querySelector('main').classList.remove('styleguide--${family}');`);
    }
    await send('Emulation.setDeviceMetricsOverride',{width:1280,height:900,deviceScaleFactor:1,mobile:false});


    const panelExample = suffix => '<section class="styleguide-section styleguide-section--full" id="strate-panel-'+suffix+'"><div class="styleguide-demo"><div class="styleguide-strate-toolbar" data-styleguide-strate-tools><h2 class="styleguide-section-title"><code>Strate '+suffix+'</code></h2><div class="styleguide-strate-menu"><button type="button" data-styleguide-panel-toggle aria-expanded="false" aria-controls="options-panel-'+suffix+'">Options</button><button type="button" data-styleguide-panel-toggle aria-expanded="false" aria-controls="readme-panel-'+suffix+'">README</button></div><div class="styleguide-strate-panel" data-styleguide-panel data-lenis-prevent id="options-panel-'+suffix+'" role="region" aria-label="Options" tabindex="-1" hidden><input type="text" aria-label="Exemple"></div><div class="styleguide-strate-panel" data-styleguide-panel="readme" data-lenis-prevent id="readme-panel-'+suffix+'" role="region" aria-label="README" tabindex="-1" hidden><div class="styleguide-readme"><h4>Documentation</h4>'+Array.from({length:60},()=>'<p>Utilisation et documentation de la strate.</p>').join('')+'</div></div></div><div class="styleguide-strate-preview" style="height:360px">Exemple</div></div></section>';
    await evaluate('document.querySelector("main").innerHTML='+JSON.stringify(panelExample('one')+panelExample('two'))+';document.querySelector("main").classList.add("styleguide--strates");window.cleanup=initStyleguide(document.querySelector("main"));window.scrollTo({top:0,behavior:"instant"});');
    assert.equal(await evaluate(`document.querySelectorAll('[data-styleguide-panel]:not([hidden])').length`),0,'Panels start closed');
    const previewTop=await evaluate(`document.querySelector('.styleguide-strate-preview').getBoundingClientRect().top`);
    await evaluate(`document.querySelector('[aria-controls="options-panel-one"]').click()`);
    assert.equal(await evaluate(`document.getElementById('options-panel-one').hidden`),false,'Options opens from the title menu');
    assert.equal(await evaluate(`document.activeElement===document.querySelector('#options-panel-one input')`),true,'Focus enters the controls');
    assert.equal(await evaluate(`getComputedStyle(document.getElementById('options-panel-one')).position`),'absolute','Panel overlays the example');
    assert.ok(await evaluate(`(()=>{const title=document.querySelector('.styleguide-strate-toolbar .styleguide-section-title').getBoundingClientRect();const button=document.querySelector('[aria-controls="options-panel-one"]').getBoundingClientRect();return Math.abs((title.top+title.bottom)-(button.top+button.bottom))<1;})()`),'Title and tabs are vertically aligned');
    assert.ok(await evaluate(`(()=>{const button=document.querySelector('[aria-controls="options-panel-one"]');const panel=document.getElementById('options-panel-one');return Math.abs(button.getBoundingClientRect().bottom-panel.getBoundingClientRect().top)<=1.1 && getComputedStyle(button).backgroundColor===getComputedStyle(panel).backgroundColor;})()`),'The active tab meets the panel and shares its background');
    assert.ok(Math.abs(await evaluate(`document.querySelector('.styleguide-strate-preview').getBoundingClientRect().top`)-previewTop)<1,'Opening a panel does not move the example');
    await evaluate(`document.querySelector('[aria-controls="readme-panel-one"]').click()`);
    assert.equal(await evaluate(`document.getElementById('options-panel-one').hidden`),true,'Switching to README closes options');
    assert.equal(await evaluate(`document.querySelector('[aria-controls="options-panel-one"]').getAttribute('aria-expanded')`),'false');
    assert.equal(await evaluate(`document.activeElement.id`),'readme-panel-one','README panel receives keyboard focus');
    await wait(`Math.abs(window.innerHeight - document.getElementById('readme-panel-one').getBoundingClientRect().bottom - 16)<2`);
    assert.equal(await evaluate(`getComputedStyle(document.querySelector('main')).overflowY`),'visible','The root does not clip the open panel');
    const openingHeight=await evaluate(`getComputedStyle(document.getElementById('readme-panel-one')).maxHeight`);
    await send('Emulation.setDeviceMetricsOverride',{width:1280,height:600,deviceScaleFactor:1,mobile:false});
    await evaluate(`new Promise(resolve=>requestAnimationFrame(()=>requestAnimationFrame(resolve)))`);
    assert.equal(await evaluate(`getComputedStyle(document.getElementById('readme-panel-one')).maxHeight`),openingHeight,'Resize does not change the opening height');
    await send('Emulation.setDeviceMetricsOverride',{width:1280,height:900,deviceScaleFactor:1,mobile:false});
    await evaluate(`window.scrollBy({top:30,behavior:'instant'});new Promise(resolve=>requestAnimationFrame(()=>requestAnimationFrame(resolve)))`);
    assert.equal(await evaluate(`getComputedStyle(document.getElementById('readme-panel-one')).maxHeight`),openingHeight,'Scroll does not change the opening height');
    const lenisSource=readFileSync(directory+'/assets/vendors/lenis/lenis.mjs','utf8');
    await evaluate(`(async()=>{const {default:Lenis}=await import(${JSON.stringify('data:text/javascript;base64,')}+${JSON.stringify(Buffer.from(lenisSource).toString('base64'))});window.lenis=new Lenis({autoRaf:true,lerp:0.14});})()`);
    const pageBeforeWheel=await evaluate('window.scrollY');
    const wheelPoint=await evaluate(`(()=>{const b=document.getElementById('readme-panel-one').getBoundingClientRect();return {x:b.left+b.width/2,y:b.top+Math.min(120,b.height/2)};})()`);
    await send('Input.dispatchMouseEvent',{type:'mouseWheel',x:wheelPoint.x,y:wheelPoint.y,deltaX:0,deltaY:220});
    await wait(`document.getElementById('readme-panel-one').scrollTop > 20`);
    assert.ok(Math.abs(await evaluate('window.scrollY')-pageBeforeWheel)<1,'README scroll is native and does not move the Lenis page');
    await evaluate('lenis.destroy();delete window.lenis;');
    await send('Input.dispatchKeyEvent',{type:'keyDown',key:'Escape',code:'Escape',windowsVirtualKeyCode:27});
    await send('Input.dispatchKeyEvent',{type:'keyUp',key:'Escape',code:'Escape',windowsVirtualKeyCode:27});
    assert.equal(await evaluate(`document.getElementById('readme-panel-one').hidden`),true,'Escape closes README');
    assert.equal(await evaluate(`document.getElementById('readme-panel-one').style.getPropertyValue('--styleguide-panel-height')`),'','Closing clears the measured height');
    assert.equal(await evaluate(`document.activeElement.getAttribute('aria-controls')`),'readme-panel-one','Escape returns focus to its toggle');
    await evaluate(`document.querySelector('[aria-controls="options-panel-one"]').click();document.querySelector('[aria-controls="options-panel-two"]').click()`);
    assert.equal(await evaluate(`document.querySelectorAll('[data-styleguide-panel]:not([hidden])').length`),1,'Only one panel is open across examples');
    await evaluate(`document.querySelector('[aria-controls="options-panel-two"]').click()`);
    assert.equal(await evaluate(`document.activeElement.getAttribute('aria-controls')`),'options-panel-two','Closing from the active tab retains focus');
    await evaluate(`document.querySelector('[aria-controls="readme-panel-one"]').click();document.body.dispatchEvent(new PointerEvent('pointerdown',{bubbles:true}));`);
    assert.equal(await evaluate(`document.getElementById('readme-panel-one').hidden`),true,'Outside pointer closes the panel');
    await send('Emulation.setDeviceMetricsOverride',{width:390,height:900,deviceScaleFactor:1,mobile:false});
    await evaluate(`document.querySelector('[aria-controls="options-panel-one"]').click()`);
    assert.equal(await evaluate(`(()=>{const b=document.getElementById('options-panel-one').getBoundingClientRect();return b.left>=0 && b.right<=document.documentElement.clientWidth;})()`),true,'Panel stays within a mobile viewport');
    await evaluate(`document.querySelector('[aria-controls="options-panel-one"]').click()`);
    assert.equal(await evaluate(`document.getElementById('options-panel-one').hidden`),true,'Clicking the same toggle closes its panel');
    await evaluate(`document.querySelector('[aria-controls="options-panel-one"]').click();cleanup()`);
    assert.equal(await evaluate(`document.querySelectorAll('[data-styleguide-panel]:not([hidden])').length`),0,'Cleanup hides panels');
    assert.equal(await evaluate(`document.querySelectorAll('[data-styleguide-panel-toggle][aria-expanded="true"]').length`),0,'Cleanup restores toggle state');
    await send('Emulation.setDeviceMetricsOverride',{width:1280,height:900,deviceScaleFactor:1,mobile:false});

    const points=[...readFileSync(directory+'/assets/styles/breakpoints.scss','utf8').matchAll(/^\$breakpoint-([a-z]+):\s*(\d+)px;/gm)].map(match=>({name:match[1],width:Number(match[2])}));
    const breakpointFixture='<table class="styleguide-breakpoints"><tbody>'+points.map((point,index)=>'<tr data-breakpoint="'+point.name+'" data-breakpoint-min="'+point.width+'"'+(points[index+1]?' data-breakpoint-max="'+(points[index+1].width-1)+'"':'')+'><td>'+point.name+'</td></tr>').join('')+'</tbody></table><aside class="styleguide-breakpoint-indicator" data-styleguide-breakpoint hidden><strong data-breakpoint-name></strong><span data-breakpoint-range></span><span data-styleguide-viewport></span></aside>';
    await evaluate(`document.querySelector('main').innerHTML=${JSON.stringify(breakpointFixture)};document.querySelector('[data-styleguide-breakpoint]').dataset.breakpoints=${JSON.stringify(JSON.stringify(points))};window.cleanup=initStyleguide(document.querySelector('main'));`);
    const colorChannels = color => {
        const values=color.match(/[\d.]+/g).map(Number);
        return [...values.slice(0,3).map(value=>color.startsWith('color(srgb')?Math.round(value*255):value),values[3]??1];
    };
    const backgrounds=new Set();
    for (const point of points) {
        await send('Emulation.setDeviceMetricsOverride',{width:point.width,height:900,deviceScaleFactor:1,mobile:false});
        await wait(`document.querySelector('[data-styleguide-breakpoint]').dataset.breakpoint === '${point.name}'`);
        const colors=await evaluate(`(()=>{const indicator=document.querySelector('[data-styleguide-breakpoint]');const row=document.querySelector('.styleguide-breakpoints .is-active');return {row:getComputedStyle(row).backgroundColor,indicator:getComputedStyle(indicator).backgroundColor,name:row.dataset.breakpoint};})()`);
        assert.equal(colors.name,point.name,'Correct breakpoint is active at its exact threshold');
        assert.deepEqual(colorChannels(colors.row).slice(0,3),colorChannels(colors.indicator).slice(0,3),'The table and fixed indicator share the same color');
        assert.equal(colorChannels(colors.indicator)[3],0.4,'Indicator background uses 40% opacity');
        assert.equal(colorChannels(colors.row)[3],0.4,'Active row background uses 40% opacity');
        assert.equal(await evaluate(`getComputedStyle(document.querySelector('.styleguide-breakpoints .is-active')).outlineStyle`),'none','Active rows have no extra border');
        assert.equal(await evaluate(`Array.from(document.querySelectorAll('.styleguide-breakpoints tbody tr:not(.is-active)')).every(row=>getComputedStyle(row).backgroundColor === 'rgba(0, 0, 0, 0)')`),true,'Inactive rows have no colored background');
        backgrounds.add(colors.row);
    }
    assert.equal(backgrounds.size,points.length,'Each breakpoint has its own background');
    await send('Emulation.setDeviceMetricsOverride',{width:points[0].width-1,height:900,deviceScaleFactor:1,mobile:false});
    await wait(`document.querySelector('[data-styleguide-breakpoint]').dataset.breakpoint === 'below'`);
    assert.equal(await evaluate(`document.querySelectorAll('.styleguide-breakpoints .is-active').length`),0);
    assert.deepEqual(colorChannels(await evaluate(`getComputedStyle(document.querySelector('[data-styleguide-breakpoint]')).backgroundColor`)),[241,245,249,0.4],'Below-xs uses the same background opacity');
    await evaluate('cleanup()');
    await send('Emulation.setDeviceMetricsOverride',{width:1280,height:900,deviceScaleFactor:1,mobile:false});


    // Render the real PHP controls: choices/defaults come from the starter's ACF JSON.
    const controlsHtml = execFileSync(process.env.PHP_BIN || 'php', ['-r', `
        $theme_dir=$argv[1];
        function get_template_directory(): string { return $GLOBALS['theme_dir']; }
        function esc_attr($value): string { return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
        function esc_html($value): string { return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
        require $theme_dir . '/inc/front/styleguide/strate-options.php';
        theme_styleguide_strate_options('strate-text', []);
    `, directory+'/web/wp-content/themes/starterkit-lonsdale-2027'], { encoding:'utf8' });
    const { compileString } = await import('sass');
    const optionsCss=compileString(readFileSync(directory+'/assets/styles/breakpoints.scss','utf8')+'\n'+readFileSync(directory+'/assets/styles/layout.scss','utf8')+'\n'+readFileSync(directory+'/assets/styles/classes.scss','utf8')).css;
    const optionsFixture='<style>'+optionsCss+'\n:root{--space-xs:4px;--space-sm:10px;--space-md:20px;--space-lg:40px;--color-1:#111;--color-2:#c04040;--color-3:#ddd;--color-text:#111;--color-text-invert:#fff;--layout:[full] 20px [fluid ctr ctr-sm] minmax(0,1fr) [ctr-sm-end ctr-end fluid-end] 20px [full-end]}</style>'+[1,2,3].map(index=>'<section class="styleguide-section styleguide-section--full" id="options-example-'+index+'"><div class="styleguide-strate-preview">'+(index<3?'<section class="strate strate-text columns-2"'+(index===2?' id="seconde-strate"':'')+' data-original="retained"><div><button type="button">Interactive content</button></div></section>':'')+'</div><div class="styleguide-strate-toolbar" data-styleguide-strate-tools><button type="button" data-styleguide-panel-toggle aria-controls="live-options-'+index+'" aria-expanded="false">Options</button><div class="styleguide-strate-panel" data-styleguide-panel data-lenis-prevent id="live-options-'+index+'" role="region" aria-label="Options" tabindex="-1" hidden>'+controlsHtml.replaceAll('options-strate-text','options-strate-text-'+index)+'</div></div></section>').join('');
    await evaluate('document.querySelector("main").innerHTML='+JSON.stringify(optionsFixture)+';window.cleanup=initStyleguide(document.querySelector("main"));');
    await evaluate(`window.firstForm=document.querySelector('#options-example-1 form');window.secondForm=document.querySelector('#options-example-2 form');window.example=document.querySelector('#options-example-1 .strate');window.contentButton=example.querySelector('button');example.classList.add('is-ready');window.setOption=(name,value,form=firstForm)=>{const control=form.querySelector('[data-strate-option="'+name+'"]');if(control.type==='checkbox')control.checked=value;else control.value=value;control.dispatchEvent(new Event('input',{bubbles:true}));};`);
    assert.equal(await evaluate(`firstForm.querySelector('[data-strate-option="margin.bottom"]').value`),'md','Options use the ACF default');
    assert.equal(await evaluate(`firstForm.querySelector('[data-strate-option="background.color"]').disabled`),true,'Background fields start disabled');
    assert.equal(await evaluate(`document.querySelectorAll('#options-example-3 [data-strate-option]:not(:disabled)').length`),0,'Empty demos keep their controls disabled');
    await evaluate(`document.querySelector('[aria-controls="live-options-1"]').click()`);
    await evaluate(`setOption('container','full');setOption('margin.top','lg');setOption('margin.bottom','0');setOption('background.hasbackground',true);setOption('background.color','color-2');setOption('background.padding.top','xs');setOption('background.padding.bottom','0');`);
    assert.equal(await evaluate(`example.classList.contains('ctr-full') && example.classList.contains('mt-lg') && example.classList.contains('mb-0') && example.classList.contains('bg-color-2') && example.classList.contains('pt-xs') && example.classList.contains('pb-0')`),true,'Changes apply the common option classes');
    assert.deepEqual(await evaluate(`(()=>{const style=getComputedStyle(example);return {top:style.marginTop,bottom:style.marginBottom,paddingTop:style.paddingTop,paddingBottom:style.paddingBottom,background:style.backgroundColor};})()`),{top:'40px',bottom:'0px',paddingTop:'4px',paddingBottom:'0px',background:'rgb(192, 64, 64)'},'Changes affect the rendered preview');
    assert.equal(await evaluate(`document.getElementById('live-options-1').hidden`),false,'Editing options keeps the overlay open');
    assert.equal(await evaluate(`firstForm.querySelector('[data-strate-option="name"]')`),null,'Anchor field is absent');
    assert.deepEqual(await evaluate(`Array.from(firstForm.querySelectorAll('.styleguide-strate-options-group > h3'),node=>node.textContent)`),['Conteneur','Marges','Background'],'Options have three named groups');
    assert.equal(await evaluate(`(()=>{const groups=[...firstForm.querySelectorAll('.styleguide-strate-options-group')];return groups.every((group,index)=>index===0 || group.getBoundingClientRect().top>=groups[index-1].getBoundingClientRect().bottom);})()`),true,'The three groups are stacked vertically');
    assert.equal(await evaluate(`example.querySelector('button')===contentButton && example.classList.contains('is-ready') && example.classList.contains('columns-2') && example.dataset.original==='retained'`),true,'Content, module classes and attributes are preserved');
    assert.equal(await evaluate(`document.querySelector('#options-example-2 .strate').className`),'strate strate-text columns-2','Each example is independent');
    await evaluate(`setOption('background.hasbackground',false)`);
    assert.equal(await evaluate(`[...example.classList].some(name=>/^(bg-|pt-|pb-)/.test(name))`),false,'Disabling the background removes its related options');
    assert.equal(await evaluate(`firstForm.dispatchEvent(new Event('submit',{bubbles:true,cancelable:true}))`),false,'Controls never submit to WordPress');
    await evaluate(`firstForm.reset();new Promise(resolve=>queueMicrotask(resolve))`);
    assert.equal(await evaluate(`example.className`),'strate strate-text columns-2 is-ready','Reset restores original options without dropping runtime classes');
    assert.equal(await evaluate(`example.hasAttribute('id')`),false);
    assert.equal(await evaluate(`firstForm.querySelector('[data-strate-option="background.color"]').disabled`),true);
    await evaluate(`setOption('container','fluid',secondForm);cleanup()`);
    assert.equal(await evaluate(`document.querySelector('#options-example-2 .strate').id`),'seconde-strate','Options leave existing IDs unchanged');
    assert.equal(await evaluate(`document.querySelector('#options-example-2 .strate').classList.contains('ctr-fluid')`),false,'Cleanup removes preview classes');

    // Exercise a real reload rather than remounting a synthetic DOM.
    fixtureHtml = '<!doctype html><html><head><style>'+css+'</style></head><body><main class="styleguide"><header style="height:300px">Components</header><nav class="styleguide-nav styleguide-nav--search"><input class="styleguide-search" type="search" data-styleguide-search aria-label="Rechercher un exemple"><ul><li><a href="#component-accordion">Accordion</a></li><li><a href="#component-text">Text</a></li></ul></nav><p data-styleguide-empty role="status" hidden>Aucun exemple trouvé.</p><div class="st-components"><section id="component-accordion" class="styleguide-section" style="min-height:1000px">Accordion</section><section id="component-text" class="styleguide-section" style="min-height:1600px">Text</section></div></main><script>'+js+';window.cleanup=initStyleguide(document.querySelector("main"));</script></body></html>';
    await send('Emulation.setEmulatedMedia',{features:[{name:'prefers-reduced-motion',value:'reduce'}]});
    await send('Page.navigate',{url:fixtureOrigin+'/components/?preserved=1'});
    await wait('!!document.querySelector("#component-text") && document.readyState === "complete"');
    await evaluate(`const input=document.querySelector('[data-styleguide-search]');input.value='text';input.dispatchEvent(new Event('input',{bubbles:true}));document.querySelector('a[href="#component-text"]').click();`);
    assert.equal(await evaluate(`new URL(location.href).searchParams.get('styleguide_search')`),'text');
    assert.equal(await evaluate(`new URL(location.href).searchParams.get('preserved')`),'1','Search preserves unrelated query parameters');
    assert.equal(await evaluate('location.hash'),'#component-text');
    const priorGeneration=await evaluate('fixtureGeneration');
    await send('Page.reload',{ignoreCache:true});
    await wait(`window.fixtureGeneration > ${priorGeneration} && document.readyState === 'complete'`);
    await wait(`Math.abs(document.getElementById('component-text').getBoundingClientRect().top - parseFloat(getComputedStyle(document.querySelector('main')).getPropertyValue('--styleguide-demo-top'))) < 2`);
    assert.equal(await evaluate(`document.querySelector('[data-styleguide-search]').value`),'text','Reload restores the search');
    assert.equal(await evaluate(`document.getElementById('component-accordion').hidden`),true,'Reload retains the filtered demos');
    assert.equal(await evaluate('location.hash'),'#component-text','Reload retains the selected anchor');
    await evaluate('cleanup()');

    // An unfiltered bookmarked anchor must also survive loading with Lenis enabled.
    fixtureHtml=fixtureHtml.replace(';window.cleanup=initStyleguide', ';window.lenisCalls=[];window.lenis={scrollTo(top,options){lenisCalls.push(options);window.scrollTo({top,behavior:"instant"});}};window.cleanup=initStyleguide');
    await send('Page.navigate',{url:fixtureOrigin+'/components/#component-text'});
    await wait(`document.readyState === 'complete' && Array.isArray(window.lenisCalls) && window.lenisCalls.length > 0`);
    assert.equal(await evaluate(`document.querySelector('[data-styleguide-search]').value`),'','A bare anchor does not restore an unrelated filter');
    assert.equal(await evaluate(`document.getElementById('component-accordion').hidden`),false);
    assert.equal(await evaluate(`lenisCalls.at(-1).immediate`),true,'Initial anchor restoration uses immediate Lenis scrolling');
    await wait(`Math.abs(document.getElementById('component-text').getBoundingClientRect().top - parseFloat(getComputedStyle(document.querySelector('main')).getPropertyValue('--styleguide-demo-top'))) < 2`);
    await evaluate('cleanup()');
    console.log('Styleguide: navigation, search, reload anchors, breakpoint colors, strate overlay panels and full-width heroes/strates and live strate options OK.');
} finally {
    clearTimeout(timeout);
    socket?.close();
    child.kill('SIGTERM');
    if (child.exitCode === null && child.signalCode === null) await new Promise(resolve => child.once('exit', resolve));
    rmSync(profile, { recursive: true, force: true });
    await new Promise(resolve=>fixtureServer.close(resolve));
}
