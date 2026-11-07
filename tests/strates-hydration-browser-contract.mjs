import { createServer } from "node:http";

import { readFile, mkdtemp, rm } from "node:fs/promises";

import { spawn } from "node:child_process";

import path from "node:path";

import assert from "node:assert/strict";

const base = process.cwd();

let requested = [];

const page = `<!doctype html><meta charset="utf-8"><body><pre id="result">PENDING</pre><script type="module">\nconst delay=ms=>new Promise(r=>setTimeout(r,ms));const errors=[];console.error=(...a)=>errors.push(a.map(String).join(' '));\ntry{const h=await import('/assets/modules/hydrate/hydrate.js');\nconst bad=document.createElement('div');bad.dataset.module='strates/strate-pushes-more/strate-pushes-more';document.body.append(bad);h.hydrateElement(bad);await delay(50);\nconst invalidRejected=!bad.dataset.hydrated&&errors.some(e=>e.includes('Invalid module name'));\nconst changing=document.createElement('div');changing.dataset.module='components/accordion';document.body.append(changing);h.hydrateElement(changing);changing.dataset.module='components/tooltip';h.hydrateElement(changing);await delay(600);h.hydrateElement(changing);await delay(200);const changedModuleStuck=!changing.dataset.hydrated;\nh.destroy(changing);\nconst nativeMatchMedia=window.matchMedia.bind(window);let matches=true;let mediaListener;window.matchMedia=query=>query==='(audit)'?{get matches(){return matches},addEventListener(type,fn){mediaListener=fn},removeEventListener(){mediaListener=null}}:nativeMatchMedia(query);\nconst media=document.createElement('div');media.dataset.module='components/accordion';media.dataset.moduleMedia='(audit)';document.body.append(media);h.hydrateElement(media);matches=false;await delay(100);const initializedOutsideMedia=media.dataset.hydrated==='true';matches=true;mediaListener?.();await delay(100);const mediaRearmed=media.dataset.hydrated==='true';h.destroy(media);window.matchMedia=nativeMatchMedia;\nconst stop=h.observe(document.body);const root=document.createElement('div');root.dataset.module='components/accordion';root.innerHTML='<div class="details"><h3><button class="summary" aria-controls="panel" aria-expanded="false">Toggle</button></h3><div class="details-content" id="panel" aria-hidden="true">Text</div></div>';document.body.append(root);await delay(100);const button=root.querySelector('button');button.click();const initialized=root.dataset.hydrated==='true'&&button.getAttribute('aria-expanded')==='true';\nconst holder=document.createElement('div');document.body.append(holder);holder.append(root);await delay(30);const survivesMove=root.dataset.hydrated==='true';root.remove();await delay(30);button.click();const removedCleanly=!root.dataset.hydrated&&button.getAttribute('aria-expanded')==='true';\nconst pending=document.createElement('div');pending.dataset.module='components/tooltip';document.body.append(pending);h.hydrateElement(pending);pending.remove();await delay(600);const detachedNeverInitialized=!pending.dataset.hydrated;\nconst makeMore=(id)=>{const el=document.createElement('section');el.dataset.module='strates/strate-pushes-more';el.dataset.pushesMoreInstance=id;el.innerHTML='<ul class="strate-list"><li>First</li></ul><a class="pushes-more-next" href="/next">More</a>';document.body.append(el);return el;};\nconst a=makeMore('1'),b=makeMore('2');await delay(400);a.querySelector('a').click();await delay(300);const moreIsolated=a.querySelectorAll('li').length===2&&b.querySelectorAll('li').length===1&&!a.querySelector('a');\nconst c=makeMore('3');await delay(100);let aborted=false;const nativeFetch=window.fetch;window.fetch=(url,{signal})=>new Promise((resolve,reject)=>signal.addEventListener('abort',()=>{aborted=true;reject(new DOMException('Aborted','AbortError'))}));c.querySelector('a').click();c.remove();await delay(50);window.fetch=nativeFetch;\nstop();document.querySelector('#result').textContent=JSON.stringify({invalidRejected,changedModuleStuck,initializedOutsideMedia,mediaRearmed,initialized,survivesMove,removedCleanly,detachedNeverInitialized,moreIsolated,aborted});\n}catch(e){document.querySelector('#result').textContent='ERROR: '+e.stack;}\n<\/script>`;

const server = createServer(async (req, res) => {
    try {
        const u = new URL(req.url, "http://localhost");
        if (u.pathname === "/next") {
            res.setHeader("Content-Type", "text/html");
            res.end('<section data-pushes-more-instance="1"><ul class="strate-list"><li>Second</li></ul></section><section data-pushes-more-instance="2"><ul class="strate-list"><li>Other</li></ul></section>');
            return;
        }
        if (u.pathname === "/") {
            res.setHeader("Content-Type", "text/html");
            res.end(page);
            return;
        }
        const file = path.resolve(base, "." + u.pathname);
        if (!file.startsWith(base + "/assets/")) throw Error();
        requested.push(u.pathname);
        const data = await readFile(file);
        res.setHeader("Content-Type", "text/javascript");
        if (u.pathname.endsWith("/accordion.js") || u.pathname.endsWith("/tooltip.js")) {
            setTimeout(() => res.end(data), 250);
        } else res.end(data);
    } catch {
        res.writeHead(404).end();
    }
});

await new Promise(r => server.listen(0, "127.0.0.1", r));

const profile = await mkdtemp("/private/tmp/hydrate-chrome-");

try {
    const child = spawn("/Applications/Google Chrome.app/Contents/MacOS/Google Chrome", [ "--headless=new", "--no-first-run", "--disable-gpu", "--user-data-dir=" + profile, "--dump-dom", "--virtual-time-budget=7000", "http://127.0.0.1:" + server.address().port ], {
        stdio: [ "ignore", "pipe", "ignore" ]
    });
    let output = "";
    child.stdout.on("data", d => output += d);
    const timeout = setTimeout(() => child.kill("SIGKILL"), 3e4);
    const code = await new Promise((r, j) => {
        child.on("error", j);
        child.on("exit", r);
    });
    clearTimeout(timeout);
    assert.equal(code, 0);
    const match = output.match(/<pre id="result">([^<]+)<\/pre>/);
    assert.ok(match);
    const result = JSON.parse(match[1]);
    assert.equal(result.changedModuleStuck, false);
    assert.equal(result.initializedOutsideMedia, false);
    for (const [key, value] of Object.entries(result)) {
        if (![ "changedModuleStuck", "initializedOutsideMedia" ].includes(key)) assert.equal(value, true, key);
    }
    console.log("Strates hydration browser contract: PASS", result);
} finally {
    await new Promise(r => server.close(r));
    await rm(profile, {
        recursive: true,
        force: true
    });
}
