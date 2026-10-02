const MOBILE_MEDIA = '(max-width: 767px)';

function Submenus(root) {
    const mobile = window.matchMedia(MOBILE_MEDIA);
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
        if (heightFrame) return;
        heightFrame = requestAnimationFrame(() => {
            heightFrame = 0;
            measureHeights();
        });
    };
    if (submenus.length) {
        window.addEventListener('resize', scheduleHeights, { passive: true });
        document.fonts?.ready.then(scheduleHeights);
        const sizeObserver = new ResizeObserver(scheduleHeights);
        submenus.forEach((submenu) => sizeObserver.observe(submenu));
        measureHeights();
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
        icon.textContent = '+';
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
            icon.textContent = open ? '−' : '+';
            if (!open) {
                for (const [child, close] of controls) if (parent !== child && parent.contains(child)) close(false);
            }
        };
        controls.set(parent, setOpen);
        button.addEventListener('click', () => {
            const open = !parent.classList.contains('is-submenu-open');
            setOpen(open);
        });
        parent.addEventListener('pointerenter', (event) => {
            if (!mobile.matches && event.pointerType === 'mouse') setOpen(true);
        });
        parent.addEventListener('pointerleave', () => {
            if (!mobile.matches && !parent.contains(document.activeElement)) setOpen(false);
        });
        parent.addEventListener('focusin', (event) => {
            // The visible disclosure button must toggle only once, on click.
            if (!mobile.matches && event.target !== button) setOpen(true);
        });
        parent.addEventListener('focusout', (event) => {
            if (!parent.contains(event.relatedTarget)) setOpen(false);
        });
        parent.addEventListener('keydown', (event) => {
            if (event.key !== 'Escape' || !parent.classList.contains('is-submenu-open')) return;
            event.preventDefault();
            event.stopPropagation();
            if (mobile.matches || window.matchMedia('(hover: none)').matches) button.focus();
            else link.focus();
            setOpen(false);
        });
    });

    const closeAll = () => controls.forEach((close) => close(false));
    root.addEventListener('navigation-close', closeAll);
    document.addEventListener('pointerdown', (event) => {
        if (!root.contains(event.target)) closeAll();
    });
    root.querySelector('.btn-nav')?.addEventListener('click', () => {
        if (root.classList.contains('is-open')) closeAll();
    });
    mobile.addEventListener('change', closeAll);
}

function Navigation(root) {
    const button = root.querySelector('.btn-nav');
    const nav = root.querySelector('#nav');
    if (!button || !nav) return;

    const mobile = window.matchMedia(MOBILE_MEDIA);
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
            document.addEventListener('keydown', onKeydown, true);
            document.addEventListener('focusin', containFocus);
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
    button.addEventListener('click', () => setOpen(!root.classList.contains('is-open')));
    button.addEventListener('keydown', (event) => {
        if (event.key !== 'Enter' || !mobile.matches) return;
        event.preventDefault();
        if (!event.repeat) setOpen(!root.classList.contains('is-open'));
    });
    nav.addEventListener('click', (event) => {
        if (root.classList.contains('is-open') && event.target instanceof Element && event.target.closest('a')) setOpen(false);
    });
    mobile.addEventListener('change', () => setOpen(false, false));
    setOpen(false, false);
}

function HeaderScroll(root, {
    hideOnScroll = false,
    compactOnScroll = false,
    threshold = 0,
} = {}) {
    const scrollThreshold = Math.max(0, Number(threshold) || 0);
    let scrollFrame = 0;
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
        window.addEventListener('scroll', onScroll, { passive: true });
    }
    if (hideOnScroll) {
        root.addEventListener('focusin', () => {
            setClass(root, 'is-hidden', false);
        });
    }
    update();

    const enableTransitions = () => {
        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                setClass(root, 'is-scroll-ready', true);
            });
        });
    };

    if (document.readyState === 'complete') {
        enableTransitions();
    } else {
        window.addEventListener('pageshow', enableTransitions, { once: true });
    }
}

const initializedHeaders = new WeakSet();

export default function HeaderNav() {
    const headerNav = document.getElementById("header-nav-mega");

    if (!headerNav || initializedHeaders.has(headerNav)) return;
    initializedHeaders.add(headerNav);

    Submenus(headerNav);
    Navigation(headerNav);
    HeaderScroll(headerNav, {
        hideOnScroll: false,
        compactOnScroll: true,
        threshold: 100,
    });
}
