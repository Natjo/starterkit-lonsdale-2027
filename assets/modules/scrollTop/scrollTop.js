export function scrollToTop() {
    if (window.lenis) {
        window.lenis.scrollTo(0, { immediate: true, force: true });
        return;
    }

    window.scrollTo({ top: 0, left: 0, behavior: "instant" });
}

export default function initScrollTop() {
    if ("scrollRestoration" in history) {
        history.scrollRestoration = "manual";
    }

    scrollToTop();

    window.addEventListener("load", () => {
        if (window.location.hash) return;

        scrollToTop();
    }, { once: true });

    window.addEventListener("pageshow", (event) => {
        if (event.persisted) scrollToTop();
    });

    return scrollToTop;
}
