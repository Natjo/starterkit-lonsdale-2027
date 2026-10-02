import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import vm from 'node:vm';

const source = readFileSync(new URL('../../../../../assets/common/header-nav-subnav/header-nav-subnav.js', import.meta.url), 'utf8');
const code = source.slice(source.indexOf('function HeaderScroll('), source.indexOf('const initializedHeaders'));

function fixture(options) {
    let scrollY = 0;
    let reads = 0;
    const frames = [];
    const listeners = new Map();
    const focusListeners = new Map();
    const mutations = [];
    const classes = (name) => {
        const values = new Set();
        return {
            contains: (value) => values.has(value),
            toggle(value, enabled) {
                mutations.push([name, value, enabled]);
                if (enabled) values.add(value);
                else values.delete(value);
            },
        };
    };
    const document = { documentElement: { classList: classes('html') }, activeElement: null, readyState: 'complete' };
    const root = { classList: classes('root'), contains: (element) => element === root, addEventListener: (type, listener) => focusListeners.set(type, listener) };
    const window = {
        get scrollY() { reads += 1; return scrollY; },
        addEventListener: (type, listener, settings) => listeners.set(type, { listener, settings }),
    };
    const context = vm.createContext({ window, document, requestAnimationFrame: (callback) => { frames.push(callback); return frames.length; } });
    vm.runInContext(code, context);
    context.HeaderScroll(root, options);
    const flush = () => { while (frames.length) frames.shift()(); };
    flush();
    return {
        root, document, listeners, focusListeners, mutations,
        reads: () => reads,
        scroll(value, repeats = 1) {
            scrollY = value;
            for (let index = 0; index < repeats; index += 1) listeners.get('scroll')?.listener();
            flush();
        },
    };
}

const disabled = fixture({ hideOnScroll: false, compactOnScroll: false });
assert.equal(disabled.listeners.has('scroll'), false);
assert.equal(disabled.focusListeners.has('focusin'), false);
assert.equal(disabled.reads(), 0);

const compact = fixture({ hideOnScroll: false, compactOnScroll: true, threshold: 100 });
assert.equal(compact.listeners.get('scroll').settings.passive, true);
assert.equal(compact.reads(), 1); // No extra read to initialize scroll direction.
compact.scroll(150, 3);
assert.equal(compact.reads(), 2); // Three events are coalesced into one RAF.
assert.equal(compact.root.classList.contains('is-compact'), true);
const before = compact.mutations.length;
compact.scroll(180);
assert.equal(compact.mutations.length, before);
compact.scroll(50);
assert.equal(compact.root.classList.contains('is-compact'), false);
assert.deepEqual(compact.mutations.filter(([, name]) => name === 'is-compact').map(([, , state]) => state), [true, false]);
assert.deepEqual(compact.mutations.filter(([, name]) => name === 'has-compact-header').map(([, , state]) => state), [true, false]);

const hidden = fixture({ hideOnScroll: true, compactOnScroll: false, threshold: 100 });
hidden.scroll(200);
assert.equal(hidden.root.classList.contains('is-hidden'), true);
const hiddenBefore = hidden.mutations.length;
hidden.scroll(240);
assert.equal(hidden.mutations.length, hiddenBefore);
hidden.scroll(180);
assert.equal(hidden.root.classList.contains('is-hidden'), false);
hidden.scroll(250);
hidden.document.activeElement = hidden.root;
hidden.focusListeners.get('focusin')();
assert.equal(hidden.root.classList.contains('is-hidden'), false);
hidden.scroll(300);
assert.equal(hidden.root.classList.contains('is-hidden'), false);
hidden.document.activeElement = null;
hidden.scroll(350);
assert.equal(hidden.root.classList.contains('is-hidden'), true);
hidden.scroll(20);
assert.equal(hidden.root.classList.contains('is-hidden'), false);

console.log('Header subnav scroll: OK (disabled options, state changes only, passive RAF, direction, focus)');
