const WHEEL_THRESHOLD = 40;
const WHEEL_RESET_DELAY = 160;
const SCROLL_SETTLE_DELAY = 180;
const SNAP_TOLERANCE = 4;
const SCROLL_DURATION = 0.9;

const easeOutQuart = (progress) => 1 - (1 - progress) ** 4;

const directSlides = (root, selector) => {
    try {
        return [...root.querySelectorAll(selector)]
            .filter((element) => element.parentElement === root);
    } catch {
        return [];
    }
};

const findElement = (root, selector) => {
    if (typeof selector !== "string" || selector.trim() === "") return null;

    try {
        return root.querySelector(selector.trim());
    } catch {
        return null;
    }
};

const viewportOffset = (value) => {
    if (Number.isFinite(value)) return `${Math.max(0, value)}px`;
    if (typeof value !== "string" || value.trim() === "") return "50vh";

    return value.trim().replace(/(-?(?:\d+\.?\d*|\.\d+))%/g, "$1vh");
};

const addMediaListener = (media, listener) => {
    if (typeof media?.addEventListener === "function") {
        media.addEventListener("change", listener);
    } else {
        media?.addListener?.(listener);
    }
};

const removeMediaListener = (media, listener) => {
    if (typeof media?.removeEventListener === "function") {
        media.removeEventListener("change", listener);
    } else {
        media?.removeListener?.(listener);
    }
};

const wheelDistance = (event) => {
    if (event.deltaMode === WheelEvent.DOM_DELTA_LINE) return event.deltaY * 16;
    if (event.deltaMode === WheelEvent.DOM_DELTA_PAGE) return event.deltaY * window.innerHeight;
    return event.deltaY;
};

const isInteractive = (target) => target instanceof Element
    && Boolean(target.closest(
        "input, textarea, select, button, summary, a[href], [role='button'], [contenteditable]:not([contenteditable='false'])",
    ));

const hasScrollableParent = (target, root, direction) => {
    let element = target instanceof Element ? target : null;

    while (element && element !== root) {
        if (element.matches("[data-snap-ignore], [data-lenis-prevent], [data-lenis-prevent-wheel]")) {
            return true;
        }

        const { overflowY } = getComputedStyle(element);
        const scrollable = /^(auto|scroll|overlay)$/.test(overflowY)
            && element.scrollHeight > element.clientHeight + 1;

        if (scrollable) {
            const canScrollDown = element.scrollTop + element.clientHeight < element.scrollHeight - 1;
            const canScrollUp = element.scrollTop > 1;
            if ((direction > 0 && canScrollDown) || (direction < 0 && canScrollUp)) return true;
        }

        element = element.parentElement;
    }

    return false;
};

export function sectionSnap(root, {
    slideSelector = ":scope > section",
    navSelector = ":scope > .snap-nav",
    activationOffset = "50vh",
    deactivationOffset = "50vh",
    breakpoint = null,
} = {}) {
    if (!(root instanceof HTMLElement)) return;

    const selector = typeof slideSelector === "string" && slideSelector.trim() !== ""
        ? slideSelector.trim()
        : ":scope > section";
    const slides = directSlides(root, selector);
    if (slides.length < 2) return;

    const activationOffsetValue = viewportOffset(activationOffset);
    const deactivationOffsetValue = viewportOffset(deactivationOffset);
    const breakpointValue = Number.isFinite(breakpoint) && breakpoint > 0
        ? breakpoint
        : null;

    const nav = findElement(root, navSelector);
    const navEntries = nav
        ? [...nav.querySelectorAll("a[href^='#']")]
            .map((link) => {
                let target = null;

                try {
                    target = document.getElementById(decodeURIComponent(link.hash.slice(1)));
                } catch {
                    return null;
                }

                const index = slides.indexOf(target);
                return index >= 0 ? { index, link, target } : null;
            })
            .filter(Boolean)
        : [];

    const reducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)");
    const breakpointMedia = breakpointValue
        ? window.matchMedia(`(min-width: ${breakpointValue}px)`)
        : null;
    const transitionOwner = Symbol("sectionSnap");
    let targets = [];
    let active = false;
    let lifecycleResumed = false;
    let animating = false;
    let ownsLenisTransition = false;
    let wheelAmount = 0;
    let wheelDirection = 0;
    let wheelTimer = 0;
    let scrollTimer = 0;
    let transitionTimer = 0;
    let progressFrame = 0;
    let resizeFrame = 0;
    let resizeAnchorIndex = null;
    let resizeObserver = null;
    let releaseSnapSuppressed = false;
    let activeNavLink = null;
    let lockedNavIndex = null;
    let activationThreshold = 0;
    let deactivationThreshold = 0;
    let wasBeforeActivation = false;
    const supportsScrollEnd = "onscrollend" in window;

    const currentScroll = () => Number.isFinite(window.lenis?.animatedScroll)
        ? window.lenis.animatedScroll
        : window.scrollY;

    const moveNavCursor = () => {
        if (!nav || !activeNavLink) return;

        const navBox = nav.getBoundingClientRect();
        const linkBox = activeNavLink.getBoundingClientRect();
        nav.style.setProperty("--cursor-top", `${linkBox.top - navBox.top}px`);
        nav.style.setProperty("--cursor-height", `${linkBox.height}px`);
    };

    const setActiveNav = (link) => {
        if (!link || activeNavLink === link) return;

        navEntries.forEach(({ link: entryLink }) => {
            const isCurrent = entryLink === link;
            entryLink.classList.toggle("active", isCurrent);
            entryLink.parentElement?.classList.toggle("active", isCurrent);
            if (isCurrent) {
                entryLink.setAttribute("aria-current", "location");
            } else {
                entryLink.removeAttribute("aria-current");
            }
        });

        activeNavLink = link;
        moveNavCursor();
    };

    const updateNavigation = (position = currentScroll()) => {
        if (navEntries.length === 0 || targets.length !== slides.length) return;
        if (Number.isInteger(lockedNavIndex)) {
            setActiveNavForSlide(lockedNavIndex);
            return;
        }

        let current = navEntries[0];
        navEntries.forEach((entry) => {
            if (targets[entry.index] <= position + SNAP_TOLERANCE) current = entry;
        });
        setActiveNav(current.link);
    };

    const setActiveNavForSlide = (index) => {
        if (navEntries.length === 0) return;

        let current = navEntries[0];
        navEntries.forEach((entry) => {
            if (entry.index <= index) current = entry;
        });
        setActiveNav(current.link);
    };

    const updateProgress = () => {
        progressFrame = 0;
        if (!active || targets.length !== slides.length) return;

        const position = currentScroll();
        const rootBox = root.getBoundingClientRect();
        const isBeforeActivation = rootBox.top > activationThreshold;
        const crossedActivation = wasBeforeActivation
            && !isBeforeActivation
            && rootBox.bottom > deactivationThreshold;
        wasBeforeActivation = isBeforeActivation;

        if (
            crossedActivation
            && !animating
            && !reducedMotion.matches
            && typeof window.lenis?.scrollTo === "function"
        ) {
            releaseSnapSuppressed = false;
            slideTo(0, { immediate: true });
            return;
        }

        if (reducedMotion.matches) {
            slides.forEach((slide) => {
                slide.style.removeProperty("--i");
                slide.style.removeProperty("--p");
            });
            updateNavigation(position);
            return;
        }

        slides.forEach((slide, index) => {
            const target = targets[index];
            const previousTarget = targets[index - 1];
            const nextTarget = targets[index + 1];
            const isFirstSlideEntering = index === 0 && position <= target;
            const isLastSlideLeaving = index === slides.length - 1 && position >= target;
            const range = position < target
                ? target - (previousTarget ?? target - window.innerHeight)
                : (nextTarget ?? target + window.innerHeight) - target;
            let progress = 1;

            if (!isFirstSlideEntering && !isLastSlideLeaving) {
                progress = Math.max(
                    0,
                    Math.min(1, 1 - Math.abs(position - target) / Math.max(1, range)),
                );
            }

            const parallax = Math.max(
                -1,
                Math.min(1, (target - position) / Math.max(1, range)),
            );
            slide.style.setProperty("--i", progress.toFixed(4));
            slide.style.setProperty("--p", parallax.toFixed(4));
        });
        updateNavigation(position);
    };

    const requestProgress = () => {
        if (!active || progressFrame) return;
        progressFrame = requestAnimationFrame(updateProgress);
    };

    const measure = () => {
        resizeFrame = 0;
        if (!active) return;

        const anchorIndex = resizeAnchorIndex;
        resizeAnchorIndex = null;

        const thresholdProbe = document.createElement("span");
        thresholdProbe.style.cssText = "display:block;position:absolute;visibility:hidden;pointer-events:none;width:0;overflow:hidden";
        thresholdProbe.style.height = activationOffsetValue;
        root.append(thresholdProbe);
        activationThreshold = Math.max(0, thresholdProbe.getBoundingClientRect().height);
        thresholdProbe.style.height = deactivationOffsetValue;
        deactivationThreshold = Math.max(0, thresholdProbe.getBoundingClientRect().height);
        thresholdProbe.remove();
        wasBeforeActivation = root.getBoundingClientRect().top > activationThreshold;

        const scrollTop = window.scrollY;
        const maximum = Math.max(
            0,
            document.documentElement.scrollHeight - window.innerHeight,
        );

        targets = slides.map((slide) => {
            const margin = Number.parseFloat(getComputedStyle(slide).scrollMarginTop) || 0;
            return Math.min(maximum, Math.max(0, slide.getBoundingClientRect().top + scrollTop - margin));
        });

        if (
            Number.isInteger(anchorIndex)
            && Math.abs(targets[anchorIndex] - currentScroll()) > SNAP_TOLERANCE
            && slideTo(anchorIndex, { immediate: true, force: animating })
        ) {
            return;
        }

        updateProgress();
        moveNavCursor();
    };

    const requestMeasure = () => {
        if (!active) return;

        if (Number.isInteger(lockedNavIndex)) {
            resizeAnchorIndex = lockedNavIndex;
        } else if (targets.length === slides.length) {
            const { index, distance } = nearestIndex();
            if (distance <= SNAP_TOLERANCE) resizeAnchorIndex = index;
        }

        if (resizeFrame) return;
        resizeFrame = requestAnimationFrame(measure);
    };

    const finishTransition = () => {
        animating = false;
        ownsLenisTransition = false;
        lockedNavIndex = null;
        window.clearTimeout(transitionTimer);
        transitionTimer = 0;
        root.classList.remove("is-snap-scrolling");
        requestProgress();
    };

    const slideTo = (index, { immediate = false, force = false } = {}) => {
        const lenis = window.lenis;
        const slide = slides[index];
        if (!slide || reducedMotion.matches || typeof lenis?.scrollTo !== "function") return false;

        animating = true;
        ownsLenisTransition = true;
        lockedNavIndex = index;
        setActiveNavForSlide(index);
        root.classList.add("is-snap-scrolling");
        root.dispatchEvent(new CustomEvent("snap:change", {
            detail: { index, slide },
        }));

        window.clearTimeout(transitionTimer);
        transitionTimer = window.setTimeout(finishTransition, SCROLL_DURATION * 1500);

        lenis.scrollTo(slide, {
            ...(immediate
                ? { immediate: true }
                : { duration: SCROLL_DURATION, easing: easeOutQuart }),
            lock: true,
            force,
            userData: { initiator: "section-snap", index, owner: transitionOwner },
            onComplete: finishTransition,
        });

        return true;
    };

    const onNavClick = (event) => {
        const link = event.target instanceof Element
            ? event.target.closest("a[href^='#']")
            : null;
        const entry = navEntries.find(({ link: entryLink }) => entryLink === link);
        if (!entry || !nav?.contains(link)) return;

        event.preventDefault();
        setActiveNav(link);

        if (!slideTo(entry.index, { force: animating })) {
            entry.target.scrollIntoView({
                behavior: reducedMotion.matches ? "auto" : "smooth",
                block: "start",
            });
        }

        if (window.location.hash !== link.hash) {
            window.history.pushState(null, "", link.hash);
        }
    };

    const destinationIndex = (direction) => {
        if (targets.length !== slides.length) measure();
        const position = currentScroll();

        if (direction > 0) {
            return targets.findIndex((target) => target > position + SNAP_TOLERANCE);
        }

        for (let index = targets.length - 1; index >= 0; index -= 1) {
            if (targets[index] < position - SNAP_TOLERANCE) return index;
        }

        return -1;
    };

    const destinationIsReachable = (index) => {
        const target = targets[index];
        if (!Number.isFinite(target)) return false;
        return Math.abs(target - currentScroll()) <= window.innerHeight + SNAP_TOLERANCE;
    };

    const nearestIndex = () => {
        const position = currentScroll();
        let nearest = 0;
        let distance = Infinity;

        targets.forEach((target, index) => {
            const nextDistance = Math.abs(target - position);
            if (nextDistance < distance) {
                nearest = index;
                distance = nextDistance;
            }
        });

        return { index: nearest, distance };
    };

    const consume = (event) => {
        if (event.cancelable) event.preventDefault();
        event.lenisStopPropagation = true;
    };

    const resetWheel = () => {
        wheelAmount = 0;
        wheelDirection = 0;
        window.clearTimeout(wheelTimer);
        wheelTimer = 0;
    };

    const onWheel = (event) => {
        if (!active || reducedMotion.matches || typeof window.lenis?.scrollTo !== "function") return;
        if (!isRootActive()) return;
        if (event.ctrlKey || Math.abs(event.deltaX) > Math.abs(event.deltaY)) return;

        const delta = wheelDistance(event);
        const direction = Math.sign(delta);
        if (direction === 0 || hasScrollableParent(event.target, root, direction)) return;

        if (animating) {
            consume(event);
            return;
        }

        const index = destinationIndex(direction);
        if (index < 0) {
            releaseSnapSuppressed = true;
            resetWheel();
            return;
        }

        // Une strate plus haute que le viewport doit d'abord pouvoir défiler
        // naturellement. Le snap reprend lorsque la cible suivante est proche.
        if (!destinationIsReachable(index)) {
            releaseSnapSuppressed = true;
            resetWheel();
            return;
        }

        releaseSnapSuppressed = false;
        consume(event);

        if (wheelDirection !== direction) wheelAmount = 0;
        wheelDirection = direction;
        wheelAmount += Math.abs(delta);
        window.clearTimeout(wheelTimer);
        wheelTimer = window.setTimeout(resetWheel, WHEEL_RESET_DELAY);

        if (wheelAmount < WHEEL_THRESHOLD) return;
        resetWheel();
        slideTo(index);
    };

    const isRootActive = () => {
        const box = root.getBoundingClientRect();
        return box.top <= activationThreshold && box.bottom > deactivationThreshold;
    };

    const onScrollSettled = () => {
        window.clearTimeout(scrollTimer);
        scrollTimer = 0;

        if (!active || animating || reducedMotion.matches || typeof window.lenis?.scrollTo !== "function") return;
        if (releaseSnapSuppressed) {
            releaseSnapSuppressed = false;
            return;
        }
        if (!isRootActive()) return;

        if (targets.length !== slides.length) measure();
        const { index, distance } = nearestIndex();
        if (distance <= SNAP_TOLERANCE) return;
        slideTo(index);
    };

    const onScroll = () => {
        requestProgress();

        if (!active || animating || supportsScrollEnd) return;
        window.clearTimeout(scrollTimer);
        scrollTimer = window.setTimeout(onScrollSettled, SCROLL_SETTLE_DELAY);
    };

    const onKeyDown = (event) => {
        if (!active || event.defaultPrevented || event.altKey || event.ctrlKey || event.metaKey || event.shiftKey) return;
        const targetInScope = event.target === document.body
            || event.target === document.documentElement
            || (event.target instanceof Node && root.contains(event.target));
        if (!targetInScope || isInteractive(event.target) || !isRootActive()) return;

        const direction = ["ArrowDown", "PageDown"].includes(event.key)
            ? 1
            : ["ArrowUp", "PageUp"].includes(event.key)
                ? -1
                : 0;

        if (direction === 0) return;
        if (hasScrollableParent(event.target, root, direction)) return;
        if (animating) {
            event.preventDefault();
            return;
        }

        const index = destinationIndex(direction);
        if (index < 0 || !destinationIsReachable(index)) {
            releaseSnapSuppressed = true;
            return;
        }
        if (!slideTo(index)) return;

        releaseSnapSuppressed = false;
        event.preventDefault();
    };

    const stop = () => {
        if (!active) return;
        active = false;
        root.removeEventListener("wheel", onWheel);
        nav?.removeEventListener("click", onNavClick);
        window.removeEventListener("keydown", onKeyDown);
        window.removeEventListener("resize", requestMeasure);
        window.removeEventListener("scroll", onScroll);
        window.removeEventListener("scrollend", onScrollSettled);
        resizeObserver?.disconnect();
        resizeObserver = null;
        resetWheel();
        const lenis = window.lenis;
        if (
            ownsLenisTransition
            && lenis?.userData?.owner === transitionOwner
            && typeof lenis.reset === "function"
        ) {
            lenis.reset();
        }
        finishTransition();
        releaseSnapSuppressed = false;
        window.clearTimeout(scrollTimer);
        scrollTimer = 0;

        if (progressFrame) {
            cancelAnimationFrame(progressFrame);
            progressFrame = 0;
        }

        if (resizeFrame) {
            cancelAnimationFrame(resizeFrame);
            resizeFrame = 0;
        }
        resizeAnchorIndex = null;
    };

    const start = () => {
        if (active) return;
        active = true;
        root.addEventListener("wheel", onWheel, { passive: false });
        nav?.addEventListener("click", onNavClick);
        window.addEventListener("keydown", onKeyDown);
        window.addEventListener("scroll", onScroll, { passive: true });
        window.addEventListener("resize", requestMeasure, { passive: true });
        if (supportsScrollEnd) {
            window.addEventListener("scrollend", onScrollSettled, { passive: true });
        }

        if ("ResizeObserver" in window) {
            resizeObserver = new ResizeObserver(requestMeasure);
            resizeObserver.observe(root);
            slides.forEach((slide) => resizeObserver.observe(slide));
            if (nav) resizeObserver.observe(nav);
        }

        measure();
    };

    const clearProgress = () => {
        slides.forEach((slide) => {
            slide.style.removeProperty("--i");
            slide.style.removeProperty("--p");
        });
    };

    const clearNavigation = () => {
        navEntries.forEach(({ link }) => {
            link.classList.remove("active");
            link.parentElement?.classList.remove("active");
            link.removeAttribute("aria-current");
        });
        nav?.style.removeProperty("--cursor-top");
        nav?.style.removeProperty("--cursor-height");
        activeNavLink = null;
    };

    const onReducedMotionChange = () => requestProgress();

    const syncBreakpoint = () => {
        const breakpointMatches = !breakpointMedia || breakpointMedia.matches;

        if (lifecycleResumed && breakpointMatches) {
            start();
        } else {
            stop();
        }

        if (!breakpointMatches) {
            clearProgress();
            clearNavigation();
        }
    };

    const pause = () => {
        lifecycleResumed = false;
        syncBreakpoint();
    };

    const resume = () => {
        lifecycleResumed = true;
        syncBreakpoint();
    };

    addMediaListener(breakpointMedia, syncBreakpoint);
    addMediaListener(reducedMotion, onReducedMotionChange);
    resume();

    return {
        pause,
        resume,
        destroy: () => {
            pause();
            removeMediaListener(breakpointMedia, syncBreakpoint);
            removeMediaListener(reducedMotion, onReducedMotionChange);
            clearProgress();
            clearNavigation();
        },
    };
}

export default function initSectionSnap(nav) {
    if (!(nav instanceof HTMLElement) || !nav.classList.contains("snap-nav")) return;

    const root = nav.parentElement;
    if (!(root instanceof HTMLElement)) return;

    return sectionSnap(root, {
        slideSelector: ":scope > .strate",
        navSelector: ":scope > .snap-nav",
        activationOffset: "var(--header-height)",
        deactivationOffset: "var(--header-height)",
        breakpoint: 1025,
    });
}
