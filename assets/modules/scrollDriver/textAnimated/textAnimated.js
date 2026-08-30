import { scrollAnimation, stagger } from "../scrollDriver";
import { splitText } from "@modules/splitText/splitText";

const prefersReducedMotion = () =>
    window.matchMedia?.("(prefers-reduced-motion: reduce)")?.matches ?? false;

function initTextAnimation(title) {
    const { chars, restore } = splitText(title);

    if (!chars.length) {
        restore();
        return null;
    }

    const updateText = progress => stagger(title, progress);

    return scrollAnimation(title, {
        range: [85, 40],
        update(event) {
            event.timeline(0, 100, updateText);
        },
        destroy() {
            restore();
        },
    });
}

function textAnimated(el) {
    const title = el?.querySelector?.(".text-animated");
    if (!title || prefersReducedMotion()) return;

    let animation = null;
    let initialized = false;
    let paused = false;
    let disposed = false;

    const initialize = () => {
        if (initialized || paused || disposed) return;

        initialized = true;
        animation = initTextAnimation(title);
    };

    const io = typeof IntersectionObserver === "function" ? new IntersectionObserver(([entry]) => {
        if (!entry.isIntersecting) return;

        io.disconnect();
        initialize();
    }, { rootMargin: "100px 0px", threshold: 0 }) : null;

    const observe = () => {
        if (initialized) {
            animation?.resume();
            return;
        }

        if (io) {
            io.observe(title);
            return;
        }

        initialize();
    };

    const pause = () => {
        if (paused || disposed) return;

        paused = true;
        io?.disconnect();
        animation?.pause();
    };

    const resume = () => {
        if (!paused || disposed) return;

        paused = false;
        observe();
    };

    const destroy = () => {
        if (disposed) return;

        disposed = true;
        io?.disconnect();
        animation?.destroy();
    };

    observe();

    return { pause, resume, destroy };
}

export default textAnimated;
