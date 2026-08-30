function num0(x) {
    const n = Number(x);
    return Number.isFinite(n) ? n : 0;
}

function docTop(el) {
    let y = 0;
    for (let n = el; n; n = n.offsetParent) y += n.offsetTop || 0;
    return y;
}

const RANGE_PRESETS = new Map([
    ["top-top", [0, 0]],
    ["middle-middle", [50, 50]],
    ["bottom-bottom", [100, 100]],
    ["middle-bottom", [50, 100]],
    ["bottom-top", [100, 0]],
    ["top-bottom", [0, 100]],
]);

/** Cached CSS-variable writers used by `stagger()`. */
function createStyleCache() {
    const lastVars = new WeakMap();

    return {
        var(el, value, name = "--p") {
            if (!el) return;

            const property = String(name);
            const nextValue = String(value);
            let properties = lastVars.get(el);

            if (!properties) {
                properties = new Map();
                lastVars.set(el, properties);
            }

            if (properties.get(property) === nextValue) return;

            properties.set(property, nextValue);
            el.style.setProperty(property, nextValue);
        },
        clear(el) {
            if (!el) return;

            const properties = lastVars.get(el);
            if (!properties) return;

            for (const property of properties.keys()) {
                el.style.removeProperty(property);
            }

            lastVars.delete(el);
        },
    };
}

// Shared cache instance (WeakMaps won't leak after unmount / PJAX swaps).
/** @type {StyleCache} */
const style = createStyleCache();

const getScrollY = () => window.lenis?.animatedScroll ?? window.scrollY ?? 0;

/** @type {((instance: { animatedScroll?: number }) => void) | null} */
let lenisListener = null;
let lenisSource = null;

function ensureLenisBridge() {
    const lenis = window.lenis;
    if (!lenis) return false;
    if (lenisListener && lenisSource === lenis) return true;

    removeLenisBridge();

    lenisListener = (instance) => {
        const y = instance?.animatedScroll ?? getScrollY();
        sharedScrollDriver?.onScroll(y);
    };

    lenis.on("scroll", lenisListener);
    lenisSource = lenis;
    return true;
}

function removeLenisBridge() {
    if (!lenisSource || !lenisListener) return;

    lenisSource.off("scroll", lenisListener);
    lenisListener = null;
    lenisSource = null;
}

let sharedScrollDriver = null;

export function ScrollDriver() {
    if (!sharedScrollDriver || sharedScrollDriver._destroyed) {
        sharedScrollDriver = new ScrollDriverInstance();
    }

    return sharedScrollDriver;
}

export function scrollAnimation(element, {
    range = "top-bottom",
    update,
    destroy: onDestroy,
} = {}) {
    if (element?.nodeType !== 1 || typeof update !== "function") return;
    if (window.matchMedia?.("(prefers-reduced-motion: reduce)")?.matches) return;

    const driver = ScrollDriver();
    let active = false;
    let disposed = false;

    const resume = () => {
        if (disposed || active) return;

        active = Boolean(driver.add(element, range, update));
        if (active) driver.enable();
    };

    const pause = () => {
        if (!active) return;

        driver.remove(element);
        active = false;
    };

    const destroy = () => {
        if (disposed) return;

        pause();
        disposed = true;
        style.clear(element);
        onDestroy?.();
    };

    resume();

    return { pause, resume, destroy };
}

class ScrollDriverInstance {
    constructor() {
        this._destroyed = false;
        this._wh = window.innerHeight;
        this._sections = [];
        this._enabled = false;
        this._latestScrollY = window.scrollY || 0;
        this._rafId = null;
        this._updateAllOnNextFrame = false;
        this._measureOnNextFrame = false;
        this._activeSections = new Set();

        this._io = new IntersectionObserver(
            (entries) => {
                let needsUpdate = false;
                for (const entry of entries) {
                    const s = this._sectionsByEl.get(entry.target);
                    if (!s) continue;
                    entry.isIntersecting ? s.el.classList.add("viewed") : s.el.classList.remove("viewed");

                    // Track which sections are near the viewport to avoid updating everything on scroll.
                    if (entry.isIntersecting) {
                        if (!this._activeSections.has(s)) {
                            this._activeSections.add(s);
                            needsUpdate = true;
                        }
                    } else {
                        if (this._activeSections.has(s)) {
                            // One last update on leave to apply a final boundary (0/100) state if needed.
                            if (this._enabled) {
                                try { s._update(this._latestScrollY); } catch { }
                            }
                            this._activeSections.delete(s);
                            needsUpdate = true;
                        }
                    }

                    // If an element just entered the viewport, run at least one update
                    // even if the user hasn't scrolled yet.
                    if (entry.isIntersecting) needsUpdate = true;
                }
                if (this._enabled && needsUpdate) this._schedule();
            },
            { rootMargin: "100px 0px 100px 0px", threshold: 0 }
        );

        this._sectionsByEl = new Map();

        this._onResize = () => {
            this._wh = window.innerHeight;
            this._latestScrollY = getScrollY();
            this._measureOnNextFrame = true;
            this._schedule(true);
        };

        this._onScroll = () => {
            this.onScroll(getScrollY());
        };

        this._onLenisReady = () => {
            if (!this._enabled || !ensureLenisBridge()) return;
            window.removeEventListener("scroll", this._onScroll);
        };
    }

    // Public: allows external scroll drivers (e.g. Lenis).
    onScroll(scrollY) {
        if (this._destroyed || !this._enabled) return;

        const y = Number(scrollY);
        this._latestScrollY = Number.isFinite(y) ? y : 0;
        this._schedule();
    }

    _flushNow(forceAll = false) {
        if (this._rafId != null) {
            cancelAnimationFrame(this._rafId);
            this._rafId = null;
        }

        const y = this._latestScrollY;
        const updateAll = forceAll || this._updateAllOnNextFrame;
        const list = updateAll ? this._sections : this._activeSections;
        this._updateAllOnNextFrame = false;

        if (this._measureOnNextFrame) {
            this._measureOnNextFrame = false;
            for (const s of this._sections) {
                s._measure(this._wh);
            }
        }

        for (const s of list) {
            s._update(y);
        }
    }

    _schedule(forceAll = false) {
        if (forceAll) this._updateAllOnNextFrame = true;
        if (this._rafId != null) return;

        this._rafId = requestAnimationFrame(() => {
            this._rafId = null;
            this._flushNow();
        });
    }

    add(el, type = "top-bottom", animation) {
        if (this._destroyed || el?.nodeType !== 1) return null;

        const shouldRemainEnabled = this._enabled;
        if (this._sectionsByEl.has(el)) {
            this.remove(el);
        }

        const s = new SectionSection(el, type, animation);
        this._sections.push(s);
        this._sectionsByEl.set(el, s);
        this._io.observe(el);

        if (this._enabled) {
            s._measure(this._wh);
            s._update(this._latestScrollY);
        } else if (shouldRemainEnabled) {
            this.enable();
        }

        return s;
    }

    remove(el) {
        if (this._destroyed || !el) return;

        const s = this._sectionsByEl.get(el);
        if (!s) return;

        this._io.unobserve(el);
        el.classList.remove("viewed");
        this._sectionsByEl.delete(el);
        this._activeSections.delete(s);

        const index = this._sections.indexOf(s);
        if (index >= 0) {
            this._sections.splice(index, 1);
        }

        this.release();
    }

    release() {
        if (this._destroyed) return;

        if (this._sections.length === 0) {
            this.disable();
        }
    }

    refresh() {
        if (this._destroyed) return;

        this._wh = window.innerHeight;
        this._latestScrollY = getScrollY();
        this._measureOnNextFrame = false;

        for (const s of this._sections) {
            s._measure(this._wh);
        }

        if (this._enabled) {
            this._flushNow(true);
        }
    }

    enable() {
        if (this._destroyed || this._enabled) return;
        this._enabled = true;
        window.addEventListener("resize", this._onResize, { passive: true });
        window.addEventListener("lenis:ready", this._onLenisReady);

        if (!ensureLenisBridge()) {
            window.addEventListener("scroll", this._onScroll, { passive: true });
        }

        this._wh = window.innerHeight;

        for (const s of this._sections) {
            s._measure(this._wh);
        }

        this._latestScrollY = getScrollY();
        this._flushNow(true);
    }

    disable() {
        if (!this._enabled) return;
        this._enabled = false;
        window.removeEventListener("resize", this._onResize);
        window.removeEventListener("scroll", this._onScroll);
        window.removeEventListener("lenis:ready", this._onLenisReady);
        removeLenisBridge();

        if (this._rafId != null) {
            cancelAnimationFrame(this._rafId);
            this._rafId = null;
        }

        this._updateAllOnNextFrame = false;
        this._measureOnNextFrame = false;
    }

    destroy() {
        if (this._destroyed) return;
        this._destroyed = true;

        this.disable();

        for (const s of this._sections) {
            if (!s?.el) continue;
            this._io.unobserve(s.el);
            s.el.classList.remove("viewed");
        }

        this._io.disconnect();
        this._sections = [];
        this._sectionsByEl.clear();
        this._activeSections.clear();
    }
}

class SectionSection {
    constructor(el, type, animation) {
        this.el = el;
        this._type = type;
        this._animation = typeof animation === "function" ? animation : () => { };

        this._percent = 0;
        this._isGated = (typeof type === "string" && RANGE_PRESETS.has(type))
            || (Array.isArray(type) && type.length >= 2);
        this._wasInTypeRange = null;
        this._gateOk = true;
        this._gateBoundary = null;

        this._top = 0;
        this._height = 0;
        this._calc = () => 0;
    }

    _updateGate() {
        if (!this._isGated) {
            this._gateOk = true;
            this._gateBoundary = null;
            return;
        }

        const inRange = this._percent >= 0 && this._percent <= 100;
        const prev = this._wasInTypeRange;
        this._wasInTypeRange = inRange;

        if (inRange) {
            this._gateOk = true;
            this._gateBoundary = null;
            return;
        }

        this._gateOk = false;
        this._gateBoundary = prev == null || prev
            ? (this._percent > 100 ? 100 : 0)
            : null;
    }

    _measure(wh) {
        // Cache a document-relative top (offsetTop is only offsetParent-relative).
        this._top = docTop(this.el);
        this._height = this.el.clientHeight;

        const top = this._top;
        const h = this._height;

        // Range shorthand: [startPct, endPct]. Named types are aliases.
        //
        // Example: [50, 100]
        // - val=0 when element TOP is at 50% viewport
        // - val=100 when element BOTTOM is at 100% viewport
        //
        // Formula maps scrollY from startY..endY to 0..1:
        //   startY = top - (startPct/100)*wh
        //   endY   = (top + h) - (endPct/100)*wh
        //   calc   = (scrollY - startY) / (endY - startY)
        const customRange = Array.isArray(this._type) && this._type.length >= 2
            ? this._type
            : null;
        const range = customRange
            ?? RANGE_PRESETS.get(this._type)
            ?? RANGE_PRESETS.get("top-bottom");
        const a = Number(range[0]);
        const b = Number(range[1]);
        const startPct = Number.isFinite(a) ? a : 0;
        const endPct = Number.isFinite(b) ? b : 100;
        const startY = top - (startPct / 100) * wh;
        const endY = top + h - (endPct / 100) * wh;
        const denom = endY - startY || 1;

        this._calc = (scrollY) => (scrollY - startY) / denom;
    }

    _update(scrollY) {
        // percent 0..100 with 2 decimals
        this._percent = Math.round(this._calc(scrollY) * 10000) / 100;
        this._updateGate();
        this._animation(this);
    }

    timeline(start, end, onscroll) {
        const s = Number(start);
        const e = Number(end);
        const denom = e - s || 1;
        const raw = (this._percent - s) / denom;
        let value = raw;
        if (this._percent < s) value = 0;
        if (this._percent > e) value = 1;
        const out = num0(value); // clamped 0..1
        if (!this._gateOk) {
            if (this._gateBoundary != null) onscroll(this._gateBoundary / 100);
            return;
        }
        onscroll(out);
    }

}


/**
 * Stagger helper (callable):
 *
 * One-shot form (convenience, avoid calling every frame):
 * - stagger(p, val, opts?)
 */
function stagger(target = null, val = 0, opts = {}) {
    const v = Number(val);
    const value = Number.isFinite(v) ? v : 0;
    const o = opts || {};

    // Fast path: cache the DOM selection and every option that does not change
    // with the scroll. The hot path then writes only the progress variable.
    if (target?.tagName) {
        if (!stagger._cache) stagger._cache = new WeakMap();

        const selector = String(o?.selector ?? "[data-progress-item], .char, .item");
        const wantedRoot = o?.rootEl ?? target;
        let cached = stagger._cache.get(target);
        const optionsChanged = cached
            && (
                !Object.is(cached.softnessOption, o.softness)
                || !Object.is(cached.staggerOption, o.stagger)
            );

        if (
            !cached
            || cached.selector !== selector
            || cached.rootEl !== wantedRoot
            || optionsChanged
            || !cached.items.length
        ) {
            const found = Array.from(target?.querySelectorAll?.(selector) ?? []);
            const items = found.length ? found : [target];
            const state = createStaggerState(items, o, wantedRoot);

            cached = {
                selector,
                rootEl: wantedRoot,
                items,
                softnessOption: o.softness,
                staggerOption: o.stagger,
                state,
            };
            stagger._cache.set(target, cached);
        }

        applyStaggerProgress(cached.state, value);
        return;
    }

    // Fallback for arrays/NodeLists passed directly
    if (!target?.tagName && typeof target?.length === "number") {
        const list = Array.from(target || []);
        const state = createStaggerState(list, o, o?.rootEl ?? list);
        applyStaggerProgress(state, value);
        return;
    }

    // Fallback: treat as single element
    if (target) {
        const state = createStaggerState([target], o, o?.rootEl);
        applyStaggerProgress(state, value);
    }
}

function clamp01(x) {
    const n = Number(x);
    if (!Number.isFinite(n)) return 0;
    return Math.min(1, Math.max(0, n));
}



function createStaggerState(items, opts = {}, rootEl = null) {
    const list = Array.isArray(items) ? items : Array.from(items || []);
    const n = list.length;
    const denom = Math.max(1, n - 1);
    const hasSoftnessOpt = opts && (opts.softness != null || opts.stagger != null);
    let softness = Number(opts.softness);
    if (!Number.isFinite(softness)) softness = Number(opts.stagger); // legacy alias

    if (Number.isFinite(softness)) {
        // If softness is given as 0..1, treat it as "stagger span" fraction
        // and convert it to a segment softness.
        if (softness > 0 && softness < 1) softness = (denom * (1 - softness)) / softness;
    } else {
        softness = 10;
    }

    softness = Math.max(0.0001, softness);

    const roots =
        rootEl && !rootEl?.tagName && typeof rootEl?.length === "number"
            ? Array.from(rootEl || []).filter(Boolean)
            : [
                rootEl ??
                list[0]?.closest?.("p") ??
                list[0]?.parentElement ??
                list[0],
            ].filter(Boolean);

    if (!applyStaggerProgress._initedRoot) applyStaggerProgress._initedRoot = new WeakMap();
    if (!applyStaggerProgress._initedItem) applyStaggerProgress._initedItem = new WeakSet();
    const initedRoot = applyStaggerProgress._initedRoot;
    const initedItem = applyStaggerProgress._initedItem;

    if (roots.length === 1) {
        const root = roots[0];
        if (!initedRoot.get(root)) {
            for (let i = 0; i < n; i++) {
                const ch = list[i];
                if (!ch) continue;
                style.var(ch, String(i), "--i");
            }
            initedRoot.set(root, true);
        }
    } else {
        for (let i = 0; i < n; i++) {
            const ch = list[i];
            if (!ch || initedItem.has(ch)) continue;
            style.var(ch, String(i), "--i");
            initedItem.add(ch);
        }
    }

    for (const root of roots) {
        style.var(root, String(denom), "--denom");
        if (hasSoftnessOpt) {
            style.var(root, String(softness), "--softness");
        }
    }

    return { roots };
}

function applyStaggerProgress(state, val) {
    if (!state?.roots?.length) return;

    const raw = Number(val);
    const progress = clamp01(raw >= 0 && raw <= 1 ? raw : (raw / 100));

    for (const root of state.roots) {
        style.var(root, String(progress), "--p");
    }
}



const STAGGER_CLEAR_SELECTOR = "[data-progress-item], .char, .item, img";

function resetStaggerState(root) {
    if (!root) return;

    if (stagger._cache) {
        stagger._cache.delete(root);
    }

    if (!applyStaggerProgress._initedRoot || !applyStaggerProgress._initedItem) return;

    applyStaggerProgress._initedRoot.delete(root);
    applyStaggerProgress._initedItem.delete(root);
    root.querySelectorAll?.(STAGGER_CLEAR_SELECTOR)?.forEach((node) => {
        applyStaggerProgress._initedItem.delete(node);
    });
}

const clearElementStyles = style.clear.bind(style);

style.clear = (el) => {
    if (!el) return;

    clearElementStyles(el);

    if (el.querySelectorAll) {
        el.querySelectorAll(STAGGER_CLEAR_SELECTOR).forEach(clearElementStyles);
    }

    resetStaggerState(el);
};

export {
    stagger,
    style,
};
