const MOBILE_MEDIA = '(max-width: 767px)';

function Submenus(root) {
    const mobile = window.matchMedia(MOBILE_MEDIA);
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
    const controls = new Map();
    root.classList.add('has-submenu-controls');

    root.querySelectorAll('li').forEach((parent, index) => {
        const wrapper = parent.querySelector(':scope > .wrapper-level');
        const submenu = wrapper?.querySelector(':scope > .sub-menu');
        const link = parent.querySelector(':scope > a');
        if (!submenu || !link) return;

        wrapper.inert = true;

        submenu.id = `${root.id}-submenu-${index + 1}`;
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'header-submenu-toggle';
        button.setAttribute('aria-label', `${root.dataset.submenuLabel || 'Sous-menu'} ${link.textContent.trim()}`);
        button.setAttribute('aria-controls', submenu.id);
        button.setAttribute('aria-expanded', 'false');
        const icon = document.createElement('span');
        icon.textContent = '+';
        icon.setAttribute('aria-hidden', 'true');
        button.append(icon);
        parent.insertBefore(button, wrapper);

        const setOpen = (open) => {
            if (open === parent.classList.contains('is-submenu-open')) return;
            if (open) {
                for (const [sibling, close] of controls) {
                    if (sibling !== parent && sibling.parentElement === parent.parentElement) close(false);
                }
            }
            parent.classList.toggle('is-submenu-open', open);
            parent.classList.toggle('is-submenu-revealed', open && (mobile.matches || reducedMotion.matches));
            wrapper.inert = !open;
            button.setAttribute('aria-expanded', String(open));
            icon.textContent = open ? '−' : '+';
            if (!open) {
                for (const [child, close] of controls) if (parent !== child && parent.contains(child)) close(false);
            }
        };
        controls.set(parent, setOpen);
        submenu.addEventListener('transitionend', (event) => {
            if (event.target === submenu && event.propertyName === 'height' && parent.classList.contains('is-submenu-open')) {
                parent.classList.add('is-submenu-revealed');
            }
        });
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
            if (wrapper.contains(event.target)) parent.classList.add('is-submenu-revealed');
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
    document.addEventListener('pointerdown', (event) => {
        if (!root.contains(event.target)) closeAll();
    });
    root.querySelector('.btn-nav')?.addEventListener('click', () => {
        if (root.classList.contains('is-open')) closeAll();
    });
    mobile.addEventListener('change', closeAll);
    reducedMotion.addEventListener('change', () => {
        if (reducedMotion.matches) {
            for (const parent of controls.keys()) {
                if (parent.classList.contains('is-submenu-open')) parent.classList.add('is-submenu-revealed');
            }
        }
    });
}

function Navigation(root) {
    const button = root.querySelector('.btn-nav');
    const nav = root.querySelector('#nav');

    if (!button || !nav) return;

    const mobileMedia = window.matchMedia(MOBILE_MEDIA);
    let transitionTimer = 0;

    const setOpen = (open, animate = true) => {
        const changed = root.classList.contains('is-open') !== open;
        if (!changed && animate) return;

        clearTimeout(transitionTimer);
        transitionTimer = 0;
        if (root.classList.contains('is-nav-animating') !== animate) {
            root.classList.toggle('is-nav-animating', animate);
        }

        if (animate) {
            transitionTimer = window.setTimeout(() => {
                root.classList.remove('is-nav-animating');
                transitionTimer = 0;
            }, 300);
        }

        if (changed) root.classList.toggle('is-open', open);
        if (button.getAttribute('aria-expanded') !== String(open)) {
            button.setAttribute('aria-expanded', String(open));
        }
        if (nav.hasAttribute('data-lenis-prevent') !== open) {
            nav.toggleAttribute('data-lenis-prevent', open);
        }
    };

    button.addEventListener('click', () => {
        if (mobileMedia.matches) setOpen(!root.classList.contains('is-open'));
    });

    nav.addEventListener('click', (event) => {
        if (root.classList.contains('is-open') && event.target instanceof Element && event.target.closest('a')) {
            setOpen(false);
        }
    });

    root.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && root.classList.contains('is-open')) {
            setOpen(false);
            button.focus();
        }
    });

    mobileMedia.addEventListener('change', () => setOpen(false, false));
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
    const headerNav = document.getElementById("header-nav-complex");

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
