const MOBILE_MEDIA = '(max-width: 767px)';

function Submenus(root, signal) {
    const mobile = window.matchMedia(root.classList.contains('header-layout-minimal') ? '(min-width: 0px)' : MOBILE_MEDIA);
    const controls = new Map();
    root.classList.add('has-submenu-controls');

    const submenus = [...root.querySelectorAll('.nav-menu > li > .level1 > .wrapper-sub-menu > .sub-menu')];
    const measureHeights = () => {
        if (mobile.matches) return;
        const heights = submenus.map((submenu) => Math.ceil(submenu.getBoundingClientRect().height));
        submenus.forEach((submenu, index) => {
            const wrapper = submenu.parentElement.parentElement;
            const value = `${heights[index]}px`;
            if (wrapper.style.getPropertyValue('--height') !== value) {
                wrapper.style.setProperty('--height', value);
            }
        });
    };
    let heightFrame = 0;
    const scheduleHeights = () => {
        if (signal?.aborted || heightFrame) return;
        heightFrame = requestAnimationFrame(() => {
            heightFrame = 0;
            measureHeights();
        });
    };
    if (submenus.length) {
        window.addEventListener('resize', scheduleHeights, { passive: true, signal });
        document.fonts?.ready.then(scheduleHeights);
        const sizeObserver = new ResizeObserver(scheduleHeights);
        submenus.forEach((submenu) => sizeObserver.observe(submenu));
        measureHeights();
        signal?.addEventListener('abort', () => { sizeObserver.disconnect(); cancelAnimationFrame(heightFrame); }, { once: true });
    }

    submenus.forEach((submenu, index) => {
        const wrapper = submenu.parentElement.parentElement;
        const parent = wrapper.parentElement;
        const link = parent.querySelector(':scope > a');
        if (!link) return;

        wrapper.inert = true;

        if (!wrapper.id) wrapper.id = `${root.id}-submenu-${index + 1}`;
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'submenu-toggle';
        button.setAttribute('aria-label', `${root.dataset.submenuLabel || 'Sous-menu'} ${link.textContent.trim()}`);
        button.setAttribute('aria-controls', wrapper.id);
        button.setAttribute('aria-expanded', 'false');
        const icon = document.createElement('span');
        icon.className = 'submenu-caret';
        icon.setAttribute('aria-hidden', 'true');
        button.append(icon);
        parent.insertBefore(button, wrapper);

        const setOpen = (open) => {
            if (open === parent.classList.contains('is-submenu-open')) return;
            if (open) {
                measureHeights();
                for (const [sibling, close] of controls) {
                    if (sibling !== parent && sibling.parentElement === parent.parentElement) close(false);
                }
            }
            parent.classList.toggle('is-submenu-open', open);
            wrapper.inert = !open;
            button.setAttribute('aria-expanded', String(open));
            if (!open) {
                for (const [child, close] of controls) if (parent !== child && parent.contains(child)) close(false);
            }
        };
        controls.set(parent, setOpen);
        button.addEventListener('click', () => {
            const open = !parent.classList.contains('is-submenu-open');
            setOpen(open);
        }, { signal });
        parent.addEventListener('pointerenter', (event) => {
            if (!mobile.matches && event.pointerType === 'mouse') setOpen(true);
        }, { signal });
        parent.addEventListener('focusin', (event) => {
            // The visible disclosure button must toggle only once, on click.
            if (!mobile.matches && event.target !== button) setOpen(true);
        }, { signal });
        parent.addEventListener('focusout', (event) => {
            if (!parent.contains(event.relatedTarget)) setOpen(false);
        }, { signal });
        parent.addEventListener('keydown', (event) => {
            if (event.key !== 'Escape' || !parent.classList.contains('is-submenu-open')) return;
            event.preventDefault();
            event.stopPropagation();
            if (mobile.matches || window.matchMedia('(hover: none)').matches) button.focus();
            else link.focus();
            setOpen(false);
        }, { signal });
    });

    const closeAll = () => controls.forEach((close) => close(false));
    // The full-width panel belongs to the header, with space below its trigger.
    root.addEventListener('pointerleave', () => {
        if (!mobile.matches && !root.contains(document.activeElement)) closeAll();
    }, { signal });
    root.addEventListener('navigation-close', closeAll, { signal });
    document.addEventListener('pointerdown', (event) => {
        if (!root.contains(event.target)) closeAll();
    }, { signal });
    root.querySelector('.btn-nav')?.addEventListener('click', () => {
        if (root.classList.contains('is-open')) closeAll();
    }, { signal });
    mobile.addEventListener('change', closeAll, { signal });
    signal?.addEventListener('abort', () => {
        closeAll();
        root.querySelectorAll('.submenu-toggle').forEach(button => button.remove());
        root.querySelectorAll('.level1').forEach(wrapper => { wrapper.inert = false; });
        root.classList.remove('has-submenu-controls');
    }, { once: true });
}

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
    let previousScrollY = hideOnScroll ? Math.max(0, window.scrollY) : 0;

    const setClass = (element, name, enabled) => {
        if (element.classList.contains(name) !== enabled) {
            element.classList.toggle(name, enabled);
        }
    };

    const update = () => {
        const scrollY = compactOnScroll || hideOnScroll ? Math.max(0, window.scrollY) : 0;
        const compact = compactOnScroll && scrollY > scrollThreshold;
        scrollFrame = 0;
        setClass(root, 'is-compact', compact);
        setClass(document.documentElement, 'has-compact-header', compact);

        if (!hideOnScroll) {
            setClass(root, 'is-hidden', false);
            return;
        }

        if (
            scrollY <= scrollThreshold
            || root.classList.contains('is-open')
            || root.contains(document.activeElement)
        ) {
            setClass(root, 'is-hidden', false);
        } else if (scrollY > previousScrollY) {
            setClass(root, 'is-hidden', true);
        } else if (scrollY < previousScrollY) {
            setClass(root, 'is-hidden', false);
        }

        previousScrollY = scrollY;
    };

    const onScroll = () => {
        if (scrollFrame) return;
        scrollFrame = requestAnimationFrame(update);
    };

    if (compactOnScroll || hideOnScroll) {
        window.addEventListener('scroll', onScroll, { passive: true, signal });
    }
    if (hideOnScroll) {
        root.addEventListener('focusin', () => {
            setClass(root, 'is-hidden', false);
        }, { signal });
    }
    update();

    const enableTransitions = () => {
        if (signal?.aborted) return;
        transitionFrame = requestAnimationFrame(() => {
            transitionFrame = requestAnimationFrame(() => {
                setClass(root, 'is-scroll-ready', true);
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
    const headerNav = root ?? document.getElementById("header-nav-mega");
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
    Submenus(headerNav, signal);
    Navigation(headerNav, signal);
    HeaderScroll(headerNav, {
        hideOnScroll: false,
        compactOnScroll: true,
        threshold: 100,
    }, signal);
    return destroy;
}
