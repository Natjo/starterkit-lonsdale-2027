import { splitText } from "@modules/splitText/splitText";

const noop = () => {};
const ROOT_CLASS = "text-appear-root";
const ACTIVE_CLASS = "is-animated";
const CSS_VARS = ["--text-appear-delay", "--text-appear-duration", "--text-appear-stagger"];
const TYPES = new Set(["fadein", "reveal"]);
const DEFAULT_IO = { rootMargin: "100px 0px", threshold: 0 };

let reducedMotionMql = null;

const isElement = value => value?.nodeType === 1 && value.classList && value.style;

const prefersReducedMotion = () => {
    if (typeof window === "undefined" || typeof window.matchMedia !== "function") return false;

    reducedMotionMql ??= window.matchMedia("(prefers-reduced-motion: reduce)");
    return reducedMotionMql.matches;
};

const toDuration = (value, fallback = null) => {
    if (typeof value !== "number" && typeof value !== "string") return fallback;
    if (typeof value === "string" && value.trim() === "") return fallback;

    const number = Number(value);
    return Number.isFinite(number) && number >= 0 ? number : fallback;
};

const resolveNode = (root, target) => {
    if (!target) return null;

    if (typeof target === "string") {
        try {
            return root.querySelector(target);
        } catch {
            return null;
        }
    }

    if (!isElement(target)) return null;
    return target === root || root.contains(target) ? target : null;
};

const normalizeItems = (root, items) => {
    if (!Array.isArray(items)) return [];

    const blocks = [];
    const seen = new Set();

    for (const item of items) {
        if (!item || typeof item !== "object") continue;

        const node = resolveNode(root, item.el ?? item.node ?? item.selector);
        if (!node || seen.has(node)) continue;

        seen.add(node);
        blocks.push({
            node,
            type: TYPES.has(item.type) ? item.type : "reveal",
            delay: toDuration(item.delay, 0),
            duration: toDuration(item.duration),
            stagger: toDuration(item.stagger),
            skipSelector: typeof item.skipSelector === "string" ? item.skipSelector : "",
        });
    }

    return blocks;
};

const captureStyles = node => Object.fromEntries(CSS_VARS.map(property => [
    property,
    {
        value: node.style.getPropertyValue(property),
        priority: node.style.getPropertyPriority(property),
    },
]));

const restoreStyles = (node, styles) => {
    for (const property of CSS_VARS) {
        const previous = styles[property];

        if (previous.value !== "") {
            node.style.setProperty(property, previous.value, previous.priority);
        } else {
            node.style.removeProperty(property);
        }
    }
};

const applyBlock = ({ node, type, delay, duration, stagger, skipSelector }) => {
    const state = {
        hadBaseClass: node.classList.contains("text-appear"),
        hadTypeClass: node.classList.contains(`text-appear--${type}`),
        styles: captureStyles(node),
    };

    node.classList.add("text-appear", `text-appear--${type}`);
    node.style.setProperty("--text-appear-delay", `${delay}ms`);

    if (duration !== null) {
        node.style.setProperty("--text-appear-duration", `${duration}ms`);
    }

    if (stagger !== null) {
        node.style.setProperty("--text-appear-stagger", `${stagger}ms`);
    }

    const split = splitText(node, { skipSelector });

    for (let index = 0; index < split.chars.length; index++) {
        split.chars[index].style.setProperty("--i", String(index));
    }

    return { ...split, node, type, state };
};

const resetBlock = ({ restore, node, type, state }) => {
    restore();
    restoreStyles(node, state.styles);

    if (!state.hadBaseClass) node.classList.remove("text-appear");
    if (!state.hadTypeClass) node.classList.remove(`text-appear--${type}`);
};

const runAnimation = (root, blocks, isCancelled) => {
    const splits = [];
    let raf1 = 0;
    let raf2 = 0;
    let destroyed = false;

    try {
        for (const block of blocks) {
            splits.push(applyBlock(block));
        }
    } catch (error) {
        splits.forEach(resetBlock);
        throw error;
    }

    raf1 = requestAnimationFrame(() => {
        raf1 = 0;
        raf2 = requestAnimationFrame(() => {
            raf2 = 0;
            if (!isCancelled()) root.classList.add(ACTIVE_CLASS);
        });
    });

    return () => {
        if (destroyed) return;
        destroyed = true;

        if (raf1) cancelAnimationFrame(raf1);
        if (raf2) cancelAnimationFrame(raf2);
        splits.forEach(resetBlock);
    };
};

/**
 * Anime l'apparition de blocs texte découpés en graphèmes.
 *
 * @param {Element} root
 * @param {object} [options]
 * @param {Array<object>} [options.items]
 * @param {boolean} [options.whenVisible=false]
 * @param {string} [options.rootMargin="100px 0px"]
 * @param {number} [options.threshold=0]
 * @returns {() => void} Nettoyage idempotent.
 */
export default function textAppear(root, options = {}) {
    if (!isElement(root)) return noop;

    const config = options && typeof options === "object" ? options : {};
    const items = Array.isArray(config.items) ? config.items : [];
    const whenVisible = config.whenVisible === true;
    const rootMargin = typeof config.rootMargin === "string" && config.rootMargin.trim() !== ""
        ? config.rootMargin.trim()
        : DEFAULT_IO.rootMargin;
    const threshold = Math.min(1, Math.max(0, toDuration(config.threshold, DEFAULT_IO.threshold)));
    const hadRootClass = root.classList.contains(ROOT_CLASS);
    const hadActiveClass = root.classList.contains(ACTIVE_CLASS);

    root.classList.add(ROOT_CLASS);

    let cancelled = false;
    let destroyAnimation = null;
    let observer = null;

    const activate = () => root.classList.add(ACTIVE_CLASS);
    const restoreRoot = () => {
        if (!hadActiveClass) root.classList.remove(ACTIVE_CLASS);
        if (!hadRootClass) root.classList.remove(ROOT_CLASS);
    };
    const destroy = () => {
        if (cancelled) return;
        cancelled = true;

        observer?.disconnect();
        observer = null;
        destroyAnimation?.();
        destroyAnimation = null;
        restoreRoot();
    };

    if (!items.length || prefersReducedMotion()) {
        activate();
        return destroy;
    }

    const start = () => {
        if (cancelled) return;

        const blocks = normalizeItems(root, items);

        if (!blocks.length) {
            activate();
            return;
        }

        try {
            destroyAnimation = runAnimation(root, blocks, () => cancelled);
        } catch (error) {
            activate();
            console.error("[textAppear] Failed to initialize", error);
        }
    };

    if (whenVisible && typeof globalThis.IntersectionObserver === "function") {
        try {
            observer = new globalThis.IntersectionObserver(([entry]) => {
                if (!entry?.isIntersecting) return;

                observer?.disconnect();
                observer = null;
                start();
            }, { rootMargin, threshold });

            observer.observe(root);
        } catch {
            observer = null;
            start();
        }
    } else {
        start();
    }

    return destroy;
}
