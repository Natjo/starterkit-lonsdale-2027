export default root => {
    const media = root?.querySelector?.("video, iframe");

    if (!media) return;

    const poster = root.querySelector(".poster");
    const motion = window.matchMedia?.("(prefers-reduced-motion: reduce)");
    const controller = new AbortController();
    const isVideo = media instanceof HTMLVideoElement;
    const lazyAutoplay = isVideo && media.hasAttribute("data-lazy-autoplay");
    const originalControls = isVideo && media.controls;
    let observer = null;
    let visible = false;

    const pause = () => {
        if (isVideo) media.pause();
    };

    const play = () => {
        if (isVideo) media.play().catch(() => {});
    };

    const loadIframe = autoplay => {
        if (isVideo) return;

        const src = autoplay ? root.dataset.autoplaySrc : media.dataset.src;
        if (src && media.getAttribute("src") !== src) media.src = src;
    };

    const syncLazyAutoplay = () => {
        if (!lazyAutoplay) return;

        const reduced = motion?.matches === true;
        media.controls = reduced || originalControls;

        if (visible && !reduced) {
            play();
        } else {
            pause();
        }
    };

    const activate = () => {
        root.classList.add("is-playing");
        poster.hidden = true;
        media.removeAttribute("tabindex");

        if (isVideo) {
            play();
        } else {
            loadIframe(true);
        }

        requestAnimationFrame(() => media.focus());
    };

    poster?.addEventListener("click", activate, { signal: controller.signal });

    if (!poster && !isVideo) {
        const autoplay = root.dataset.autoplay === "true" && motion?.matches !== true;
        loadIframe(autoplay);
    }

    if (lazyAutoplay) {
        if ("IntersectionObserver" in window) {
            observer = new IntersectionObserver(([entry]) => {
                visible = entry.isIntersecting;
                syncLazyAutoplay();
            }, { threshold: 0.15, rootMargin: "50px 0px" });
            observer.observe(root);
        } else {
            visible = true;
            syncLazyAutoplay();
        }
    }

    motion?.addEventListener?.("change", () => {
        syncLazyAutoplay();
        if (!poster && !isVideo && root.dataset.autoplay === "true") {
            loadIframe(!motion.matches);
        }
    }, { signal: controller.signal });

    return () => {
        controller.abort();
        observer?.disconnect();
        pause();
        if (!isVideo) media.removeAttribute("src");
    };
};
