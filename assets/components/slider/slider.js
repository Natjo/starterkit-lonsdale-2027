const directChild = (element, selector) =>
    Array.from(element?.children || []).find((child) => child.matches(selector)) || null;

/**
 * Carrousel basé sur le scroll natif.
 * API publique : add(), remove(), slideTo(index), onchange, onnext et onprev.
 */
function Slider(root) {
    if (!(this instanceof Slider)) return new Slider(root);
    if (!(root instanceof HTMLElement)) return;

    const wrapper = directChild(root, ".slider-wrapper");
    const content = directChild(wrapper, ".slider-content");
    const items = Array.from(content?.children || []).filter((item) => item.matches(".item"));
    const navigation = directChild(root, ".slider-navigation");
    const previousButton = navigation?.querySelector(":scope > .prev") || null;
    const nextButton = navigation?.querySelector(":scope > .next") || null;
    const pagination = directChild(root, "[data-slider-pagination]");
    const timeline = directChild(root, "[data-slider-timeline]");
    const status = directChild(root, "[data-slider-status]");

    if (!content || items.length === 0) return;

    const api = this;
    const itemIndexes = new WeakMap(items.map((item, index) => [item, index]));
    const reducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)");
    const pageLabel = root.dataset.sliderPageLabel || "Page";
    const goToLabel = root.dataset.sliderGoToLabel || "Aller à la page";
    const state = {
        active: false,
        controller: null,
        resizeObserver: null,
        index: 0,
        targets: [0],
        itemPages: items.map(() => 0),
        dots: [],
        paginationSize: 0,
        disabled: true,
        scrollFrame: 0,
        resizeFrame: 0,
        animationFrame: 0,
        statusTimer: 0,
        animating: false,
        dragging: false,
        pointerId: null,
        dragStartX: 0,
        dragStartScroll: 0,
        dragStartPage: 0,
        suppressClick: false,
    };

    api.onchange = () => {};
    api.onnext = () => {};
    api.onprev = () => {};

    const clamp = (value, minimum, maximum) => Math.min(Math.max(value, minimum), maximum);
    const maxIndex = () => Math.max(0, state.targets.length - 1);

    const nearestPage = (position) => {
        let nearest = 0;
        let distance = Infinity;

        state.targets.forEach((target, index) => {
            const nextDistance = Math.abs(target - position);
            if (nextDistance < distance) {
                nearest = index;
                distance = nextDistance;
            }
        });

        return nearest;
    };

    const updateTimeline = () => {
        if (!timeline) return;
        const maximum = Math.max(0, content.scrollWidth - content.clientWidth);
        const progress = maximum === 0 ? 1 : clamp(content.scrollLeft / maximum, 0, 1);
        timeline.style.setProperty("--slider-progress", String(progress));
    };

    const updateControls = () => {
        const previousDisabled = state.disabled || state.index === 0;
        const nextDisabled = state.disabled || state.index === maxIndex();

        if (previousButton) {
            previousButton.disabled = previousDisabled;
            previousButton.setAttribute("aria-disabled", String(previousDisabled));
        }
        if (nextButton) {
            nextButton.disabled = nextDisabled;
            nextButton.setAttribute("aria-disabled", String(nextDisabled));
        }

        state.dots.forEach((dot, index) => {
            const active = index === state.index;
            dot.classList.toggle("is-active", active);
            dot.setAttribute("aria-current", active ? "page" : "false");
        });
    };

    const announce = () => {
        if (!status) return;
        window.clearTimeout(state.statusTimer);
        state.statusTimer = window.setTimeout(() => {
            status.textContent = `${pageLabel} ${state.index + 1} / ${state.targets.length}`;
        }, 120);
    };

    const setIndex = (index, { notify = true, announceChange = true } = {}) => {
        const nextIndex = clamp(index, 0, maxIndex());
        const changed = nextIndex !== state.index;
        state.index = nextIndex;
        updateControls();

        if (changed && notify) {
            const maximum = Math.max(1, content.scrollWidth - content.clientWidth);
            api.onchange(state.index, maxIndex(), clamp(content.scrollLeft * 100 / maximum, 0, 100));
        }
        if (changed && announceChange) announce();
    };

    const buildPagination = () => {
        if (!pagination) return;
        if (state.paginationSize === state.targets.length) return;
        pagination.replaceChildren();
        state.dots = [];

        const fragment = document.createDocumentFragment();
        state.targets.forEach((target, index) => {
            const dot = document.createElement("button");
            dot.type = "button";
            dot.className = "slider-dot";
            dot.dataset.index = String(index);
            dot.setAttribute("aria-label", `${goToLabel} ${index + 1} / ${state.targets.length}`);
            fragment.appendChild(dot);
            state.dots.push(dot);
        });
        pagination.appendChild(fragment);
        state.paginationSize = state.targets.length;
    };

    const measure = () => {
        if (!state.active) return;

        const measureBox = (wrapper || root).getBoundingClientRect();
        const viewportBox = root.ownerDocument.documentElement.getBoundingClientRect();
        const left = Math.max(0, Math.round(measureBox.left - viewportBox.left));
        const right = Math.max(0, Math.round(viewportBox.right - measureBox.right));
        root.style.setProperty("--left", `${left}px`);
        root.style.setProperty("--right", `${right}px`);

        const maximum = Math.max(0, content.scrollWidth - content.clientWidth);
        const rawTargets = items.map((item) => clamp(item.offsetLeft - left, 0, maximum));
        const targets = [];
        rawTargets.forEach((target) => {
            if (targets.every((existing) => Math.abs(existing - target) > 1)) targets.push(target);
        });
        if (targets.length === 0) targets.push(0);
        if (maximum > 1 && targets.every((target) => Math.abs(target - maximum) > 1)) targets.push(maximum);

        state.targets = targets.sort((a, b) => a - b);
        state.itemPages = rawTargets.map((target) => nearestPage(target));
        state.disabled = maximum <= 1 || state.targets.length <= 1;
        root.classList.toggle("disable", state.disabled);
        buildPagination();
        setIndex(nearestPage(content.scrollLeft), { notify: false, announceChange: false });
        updateTimeline();
    };

    const requestMeasure = () => {
        if (state.resizeFrame) return;
        state.resizeFrame = window.requestAnimationFrame(() => {
            state.resizeFrame = 0;
            measure();
        });
    };

    const stopReleaseAnimation = ({ keepSnapLock = false } = {}) => {
        window.cancelAnimationFrame(state.animationFrame);
        state.animationFrame = 0;
        state.animating = false;
        if (!keepSnapLock) {
            content.classList.remove("is-snapping");
            content.style.removeProperty("scroll-snap-type");
        }
    };

    const scrollToPage = (index, behavior = reducedMotion.matches ? "auto" : "smooth") => {
        stopReleaseAnimation();
        setIndex(index, { notify: true, announceChange: true });
        content.scrollTo({ left: state.targets[state.index] || 0, behavior });
    };

    const animateToPage = (index) => {
        stopReleaseAnimation({ keepSnapLock: true });
        setIndex(index, { notify: true, announceChange: true });

        const start = content.scrollLeft;
        const end = state.targets[state.index] || 0;
        const distance = end - start;

        if (reducedMotion.matches || Math.abs(distance) < 1) {
            content.scrollLeft = end;
            content.classList.remove("is-snapping");
            content.style.removeProperty("scroll-snap-type");
            updateTimeline();
            return;
        }

        const duration = 800;
        let startedAt = null;
        state.animating = true;
        content.classList.add("is-snapping");
        content.style.setProperty("scroll-snap-type", "none");

        const step = (timestamp) => {
            if (!state.animating) return;
            if (startedAt === null) startedAt = timestamp;

            const progress = clamp((timestamp - startedAt) / duration, 0, 1);
            const eased = 1 - Math.pow(1 - progress, 5);
            content.scrollLeft = start + distance * eased;
            updateTimeline();

            if (progress < 1) {
                state.animationFrame = window.requestAnimationFrame(step);
                return;
            }

            content.scrollLeft = end;
            state.animationFrame = 0;
            state.animating = false;
            content.classList.remove("is-snapping");
            content.style.removeProperty("scroll-snap-type");
        };

        state.animationFrame = window.requestAnimationFrame(step);
    };

    const onScroll = () => {
        if (state.scrollFrame) return;
        state.scrollFrame = window.requestAnimationFrame(() => {
            state.scrollFrame = 0;
            if (!state.animating) setIndex(nearestPage(content.scrollLeft));
            updateTimeline();
        });
    };

    const next = () => {
        scrollToPage(state.index + 1);
        api.onnext(state.index);
    };

    const previous = () => {
        scrollToPage(state.index - 1);
        api.onprev(state.index);
    };

    const onKeyDown = (event) => {
        if (event.target !== content || state.disabled) return;
        if (event.key === "ArrowLeft") {
            event.preventDefault();
            previous();
        }
        if (event.key === "ArrowRight") {
            event.preventDefault();
            next();
        }
    };

    const onFocusIn = (event) => {
        const item = event.target instanceof Element ? event.target.closest(".item") : null;
        const itemIndex = item ? itemIndexes.get(item) : undefined;
        if (itemIndex === undefined || !content.contains(item)) return;
        const page = state.itemPages[itemIndex];
        if (page !== state.index) scrollToPage(page);
    };

    const endDrag = (event) => {
        if (event && state.pointerId !== null && event.pointerId !== state.pointerId) return;

        const pointerId = state.pointerId;
        const wasDragging = state.dragging;
        state.pointerId = null;
        state.dragging = false;

        // Conserver le snap désactivé sans aucune frame intermédiaire : sinon
        // le navigateur rejoint brutalement une position avant l'animation RAF.
        if (wasDragging) {
            content.classList.add("is-snapping");
            content.style.setProperty("scroll-snap-type", "none");
        }
        content.classList.remove("is-dragging");

        if (pointerId !== null && content.hasPointerCapture?.(pointerId)) {
            content.releasePointerCapture(pointerId);
        }

        if (wasDragging) {
            state.suppressClick = true;
            const movement = content.scrollLeft - state.dragStartScroll;
            const direction = movement > 0 ? 1 : movement < 0 ? -1 : 0;
            const destination = direction === 0
                ? nearestPage(content.scrollLeft)
                : state.dragStartPage + direction;
            animateToPage(destination);
        } else {
            // Un clic peut interrompre une animation : reprendre sa destination
            // évite de laisser le slider entre deux pages.
            animateToPage(state.dragStartPage);
        }
    };

    const onPointerMove = (event) => {
        if (state.pointerId === null || event.pointerId !== state.pointerId) return;
        if (event.buttons === 0) return endDrag(event);

        const distance = event.clientX - state.dragStartX;
        if (!state.dragging && Math.abs(distance) < 6) return;
        if (!state.dragging) {
            state.dragging = true;
            content.classList.add("is-dragging");
            content.style.setProperty("scroll-snap-type", "none");
            content.setPointerCapture?.(event.pointerId);
        }
        if (event.cancelable) event.preventDefault();
        content.scrollLeft = state.dragStartScroll - distance;
    };

    const onPointerDown = (event) => {
        if (event.pointerType !== "mouse" || event.button !== 0 || state.disabled) return;
        const startPage = state.animating ? state.index : nearestPage(content.scrollLeft);

        // Ne jamais réactiver le snap entre une animation interrompue et le
        // nouveau geste, sous peine de perdre la position intermédiaire.
        content.classList.add("is-snapping");
        content.style.setProperty("scroll-snap-type", "none");
        stopReleaseAnimation({ keepSnapLock: true });
        state.pointerId = event.pointerId;
        state.dragStartX = event.clientX;
        state.dragStartScroll = content.scrollLeft;
        state.dragStartPage = startPage;
        state.dragging = false;
    };

    const onDragStart = (event) => event.preventDefault();

    const onClickCapture = (event) => {
        if (!state.suppressClick) return;
        event.preventDefault();
        event.stopPropagation();
        state.suppressClick = false;
    };

    const onPaginationClick = (event) => {
        const dot = event.target instanceof Element ? event.target.closest(".slider-dot") : null;
        if (!dot || !pagination?.contains(dot)) return;
        const index = Number(dot.dataset.index);
        if (Number.isInteger(index)) scrollToPage(index);
    };

    api.add = () => {
        if (state.active || root.__sliderInstance) return api;
        state.active = true;
        root.__sliderInstance = api;
        state.controller = new AbortController();
        const { signal } = state.controller;

        previousButton?.addEventListener("click", previous, { signal });
        nextButton?.addEventListener("click", next, { signal });
        pagination?.addEventListener("click", onPaginationClick, { signal });
        content.addEventListener("scroll", onScroll, { passive: true, signal });
        content.addEventListener("keydown", onKeyDown, { signal });
        content.addEventListener("focusin", onFocusIn, { signal });
        content.addEventListener("pointerdown", onPointerDown, { signal });
        content.addEventListener("pointermove", onPointerMove, { signal });
        content.addEventListener("pointerup", endDrag, { signal });
        content.addEventListener("pointercancel", endDrag, { signal });
        content.addEventListener("dragstart", onDragStart, { signal });
        content.addEventListener("click", onClickCapture, { capture: true, signal });

        if ("ResizeObserver" in window) {
            state.resizeObserver = new ResizeObserver(requestMeasure);
            state.resizeObserver.observe(wrapper || root);
            state.resizeObserver.observe(content);
        } else {
            window.addEventListener("resize", requestMeasure, { passive: true, signal });
        }

        measure();
        return api;
    };

    api.remove = () => {
        if (!state.active) return;
        state.active = false;
        state.controller?.abort();
        state.resizeObserver?.disconnect();
        if (state.pointerId !== null && content.hasPointerCapture?.(state.pointerId)) {
            content.releasePointerCapture(state.pointerId);
        }
        state.pointerId = null;
        state.dragging = false;
        state.suppressClick = false;
        content.classList.remove("is-dragging", "is-snapping");
        content.style.removeProperty("scroll-snap-type");
        window.cancelAnimationFrame(state.scrollFrame);
        window.cancelAnimationFrame(state.resizeFrame);
        window.cancelAnimationFrame(state.animationFrame);
        window.clearTimeout(state.statusTimer);
        pagination?.replaceChildren();
        if (status) status.textContent = "";
        timeline?.style.removeProperty("--slider-progress");
        root.style.removeProperty("--left");
        root.style.removeProperty("--right");
        root.classList.remove("disable");
        state.dots = [];
        state.paginationSize = 0;
        state.scrollFrame = 0;
        state.resizeFrame = 0;
        state.animationFrame = 0;
        state.animating = false;
        delete root.__sliderInstance;
    };

    api.slideTo = (value) => {
        const index = Number(value);
        if (Number.isFinite(index)) scrollToPage(Math.trunc(index));
    };
}

export { Slider };

export default function slider(root) {
    if (!(root instanceof HTMLElement)) return;
    const instance = new Slider(root);
    instance?.add?.();
    return () => instance?.remove?.();
}
