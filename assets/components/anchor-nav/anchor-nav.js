const resolveTarget = (link) => {
    try {
        return document.getElementById(decodeURIComponent(link.hash.slice(1)));
    } catch {
        return null;
    }
};

export default function initAnchorNav(root) {
    if (!(root instanceof HTMLElement)) return;

    const entries = [...root.querySelectorAll("a[href^='#']")]
        .map((link) => ({ link, target: resolveTarget(link) }))
        .filter(({ target }) => target && target !== root);

    if (entries.length === 0) return;

    const reducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)");
    let activeLink = null;
    let frame = null;
    let activationLine = 0;
    let running = false;
    let lockedEntry = null;
    let scrollEndTimer = null;
    let lenisScrolling = false;

    // Position et hauteur du curseur, lues par le pseudo-élément de la nav.
    const moveCursor = () => {
        if (!activeLink) return;

        const rootBox = root.getBoundingClientRect();
        const linkBox = activeLink.getBoundingClientRect();

        root.style.setProperty("--cursor-top", `${linkBox.top - rootBox.top}px`);
        root.style.setProperty("--cursor-height", `${linkBox.height}px`);
    };

    const setActive = (link) => {
        if (activeLink === link) return;

        entries.forEach(({ link: entryLink }) => {
            const active = entryLink === link;
            entryLink.classList.toggle("active", active);
            entryLink.parentElement?.classList.toggle("active", active);

            if (active) {
                entryLink.setAttribute("aria-current", "location");
            } else {
                entryLink.removeAttribute("aria-current");
            }
        });

        activeLink = link;
        moveCursor();
    };

    const update = () => {
        frame = null;

        if (lockedEntry) {
            setActive(lockedEntry.link);
            return;
        }

        let current = entries[0];

        entries.forEach((entry) => {
            if (entry.target.getBoundingClientRect().top <= activationLine + 10) current = entry;
        });

        setActive(current.link);
    };

    const finishProgrammaticScroll = (entry) => {
        if (lockedEntry !== entry) return;

        lockedEntry = null;
        lenisScrolling = false;
        clearTimeout(scrollEndTimer);
        scrollEndTimer = null;
        onScroll();
    };

    const onScroll = () => {
        if (lockedEntry && !lenisScrolling) {
            const entry = lockedEntry;
            clearTimeout(scrollEndTimer);
            scrollEndTimer = setTimeout(() => finishProgrammaticScroll(entry), 150);
        }

        if (frame !== null) return;
        frame = requestAnimationFrame(update);
    };

    const onClick = (event) => {
        const link = event.target instanceof Element
            ? event.target.closest("a[href^='#']")
            : null;

        if (!link || !root.contains(link)) return;

        const entry = entries.find(({ link: entryLink }) => entryLink === link);
        if (!entry) return;

        event.preventDefault();
        clearTimeout(scrollEndTimer);
        lockedEntry = entry;
        setActive(link);

        if (!reducedMotion.matches && window.lenis?.scrollTo) {
            lenisScrolling = true;
            window.lenis.scrollTo(entry.target, {
                onComplete: () => finishProgrammaticScroll(entry),
            });
        } else {
            lenisScrolling = false;
            entry.target.scrollIntoView({
                behavior: reducedMotion.matches ? "auto" : "smooth",
                block: "start",
            });
            scrollEndTimer = setTimeout(() => finishProgrammaticScroll(entry), 150);
        }

        if (window.location.hash !== link.hash) {
            window.history.pushState(null, "", link.hash);
        }
    };

    const onResize = () => {
        activationLine = Number.parseFloat(
            getComputedStyle(entries[0].target).scrollMarginTop,
        ) || 0;
        moveCursor();
        onScroll();
    };

    const pause = () => {
        if (!running) return;
        running = false;

        root.removeEventListener("click", onClick);
        window.removeEventListener("scroll", onScroll);
        window.removeEventListener("resize", onResize);
        lockedEntry = null;
        lenisScrolling = false;
        clearTimeout(scrollEndTimer);
        scrollEndTimer = null;

        if (frame !== null) {
            cancelAnimationFrame(frame);
            frame = null;
        }
    };

    const resume = () => {
        if (running) return;
        running = true;

        root.addEventListener("click", onClick);
        window.addEventListener("scroll", onScroll, { passive: true });
        window.addEventListener("resize", onResize);
        onResize();
    };

    resume();

    return {
        pause,
        resume,
        destroy: () => {
            pause();
            setActive(null);
            root.style.removeProperty("--cursor-top");
            root.style.removeProperty("--cursor-height");
        },
    };
}
