const MOBILE_MEDIA = '(max-width: 767px)';

function Navigation(root, signal) {
    const button = root.querySelector('.btn-nav');
    const nav = root.querySelector('#nav');
    if (!button || !nav) return;

    const mobile = window.matchMedia(root.classList.contains('header-layout-minimal') ? '(min-width: 0px)' : MOBILE_MEDIA);
    const originalTabindex = nav.getAttribute('tabindex');
    let transitionTimer = 0;
    let focusFrame = 0;
    const focusableElements = () => [...nav.querySelectorAll('a[href], button, input, select, textarea, [tabindex], [contenteditable="true"]')]
        .filter((element) => element.tabIndex >= 0 && !element.matches(':disabled')
            && !element.closest('[inert]') && element.getClientRects().length
            && getComputedStyle(element).visibility === 'visible');
    const focusFirst = () => {
        const first = focusableElements()[0];
        if (!first) nav.setAttribute('tabindex', '-1');
        (first ?? nav).focus({ preventScroll: true });
    };
    const containFocus = (event) => {
        if (!nav.contains(event.target)) focusFirst();
    };
    const onKeydown = (event) => {
        if (event.key === 'Escape') {
            event.preventDefault();
            event.stopPropagation();
            setOpen(false);
            button.focus({ preventScroll: true });
        } else if (event.key === 'Tab') {
            const items = focusableElements();
            const first = items[0];
            const last = items[items.length - 1];
            if (!first || !nav.contains(document.activeElement)
                || (event.shiftKey && (document.activeElement === first || document.activeElement === nav))
                || (!event.shiftKey && (document.activeElement === last || document.activeElement === nav))) {
                event.preventDefault();
                (event.shiftKey ? last ?? nav : first ?? nav).focus({ preventScroll: true });
            }
        }
    };
    const setOpen = (open, animate = true) => {
        open = open && mobile.matches;
        const changed = root.classList.contains('is-open') !== open;
        if (!changed && animate) return;
        clearTimeout(transitionTimer);
        cancelAnimationFrame(focusFrame);
        focusFrame = 0;
        root.classList.toggle('is-nav-animating', animate);
        if (animate) transitionTimer = window.setTimeout(() => {
            root.classList.remove('is-nav-animating');
            transitionTimer = 0;
        }, 300);
        root.classList.toggle('is-open', open);
        button.setAttribute('aria-expanded', String(open));
        nav.toggleAttribute('data-lenis-prevent', open);
        nav.inert = mobile.matches && !open;
        if (open) {
            document.addEventListener('keydown', onKeydown, { capture: true, signal });
            document.addEventListener('focusin', containFocus, { signal });
            focusFrame = requestAnimationFrame(() => {
                focusFrame = 0;
                if (!mobile.matches || !root.classList.contains('is-open')) return;
                focusFrame = requestAnimationFrame(() => {
                    focusFrame = 0;
                    if (mobile.matches && root.classList.contains('is-open')) focusFirst();
                });
            });
        } else {
            document.removeEventListener('keydown', onKeydown, true);
            document.removeEventListener('focusin', containFocus);
            root.dispatchEvent(new Event('navigation-close'));
            if (originalTabindex === null) nav.removeAttribute('tabindex');
            else nav.setAttribute('tabindex', originalTabindex);
            if (mobile.matches && nav.contains(document.activeElement)) button.focus({ preventScroll: true });
        }
    };
    button.addEventListener('click', () => setOpen(!root.classList.contains('is-open')), { signal });
    button.addEventListener('keydown', (event) => {
        if (event.key !== 'Enter' || !mobile.matches) return;
        event.preventDefault();
        if (!event.repeat) setOpen(!root.classList.contains('is-open'));
    }, { signal });
    nav.addEventListener('click', (event) => {
        if (root.classList.contains('is-open') && event.target instanceof Element && event.target.closest('a')) setOpen(false);
    }, { signal });
    mobile.addEventListener('change', () => setOpen(false, false), { signal });
    setOpen(false, false);
    signal?.addEventListener('abort', () => {
        clearTimeout(transitionTimer);
        cancelAnimationFrame(focusFrame);
        document.removeEventListener('keydown', onKeydown, true);
        document.removeEventListener('focusin', containFocus);
        root.classList.remove('is-open', 'is-nav-animating');
        nav.inert = false;
        nav.removeAttribute('data-lenis-prevent');
        button.setAttribute('aria-expanded', 'false');
        if (originalTabindex === null) nav.removeAttribute('tabindex');
        else nav.setAttribute('tabindex', originalTabindex);
    }, { once: true });
}

function HeaderScroll(root, {
    hideOnScroll = false,
    compactOnScroll = false,
    threshold = 0,
} = {}, signal) {
    const scrollThreshold = Math.max(0, Number(threshold) || 0);
    let scrollFrame = 0;
    let transitionFrame = 0;
    let previousScrollY = Math.max(0, window.scrollY);

    const update = () => {
        const scrollY = Math.max(0, window.scrollY);
        const compact = compactOnScroll && scrollY > scrollThreshold;
        scrollFrame = 0;
        root.classList.toggle('is-compact', compact);
        document.documentElement.classList.toggle('has-compact-header', compact);

        if (
            !hideOnScroll
            || scrollY <= scrollThreshold
            || root.classList.contains('is-open')
            || root.contains(document.activeElement)
        ) {
            root.classList.remove('is-hidden');
        } else if (scrollY > previousScrollY) {
            root.classList.add('is-hidden');
        } else if (scrollY < previousScrollY) {
            root.classList.remove('is-hidden');
        }

        previousScrollY = scrollY;
    };

    const onScroll = () => {
        if (scrollFrame) return;
        scrollFrame = requestAnimationFrame(update);
    };

    window.addEventListener('scroll', onScroll, { passive: true, signal });
    root.addEventListener('focusin', () => {
        root.classList.remove('is-hidden');
    }, { signal });
    update();

    const enableTransitions = () => {
        if (signal?.aborted) return;
        transitionFrame = requestAnimationFrame(() => {
            transitionFrame = requestAnimationFrame(() => {
                root.classList.add('is-scroll-ready');
            });
        });
    };

    signal?.addEventListener('abort', () => {
        cancelAnimationFrame(scrollFrame);
        cancelAnimationFrame(transitionFrame);
        document.documentElement.classList.remove('has-compact-header');
        root.classList.remove('is-compact', 'is-hidden', 'is-scroll-ready');
    }, { once: true });

    if (document.readyState === 'complete') {
        enableTransitions();
    } else {
        window.addEventListener('pageshow', enableTransitions, { once: true, signal });
    }
}

const headerInstances = new WeakMap();

export default function HeaderNav(root) {
    const headerNav = root ?? document.getElementById("header-nav");
    if (!headerNav) return;
    if (headerInstances.has(headerNav)) return headerInstances.get(headerNav);
    const controller = new AbortController();
    const { signal } = controller;
    const destroy = () => {
        if (signal.aborted) return;
        controller.abort();
        headerInstances.delete(headerNav);
    };
    headerInstances.set(headerNav, destroy);
    headerNav.addEventListener('theme:header-destroy', destroy, { once: true, signal });
    Navigation(headerNav, signal);
    HeaderScroll(headerNav, {
        hideOnScroll: false,
        compactOnScroll: true,
        threshold: 100,
    }, signal);
    return destroy;
}
