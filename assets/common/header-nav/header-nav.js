const MOBILE_MEDIA = '(max-width: 767px)';

function Navigation(root) {
    const button = root.querySelector('.btn-nav');
    const nav = root.querySelector('#nav');

    if (!button || !nav) return;

    const mobileMedia = window.matchMedia(MOBILE_MEDIA);
    let transitionTimer = 0;

    const setOpen = (open, animate = true) => {
        clearTimeout(transitionTimer);
        root.classList.toggle('is-nav-animating', animate);

        if (animate) {
            transitionTimer = window.setTimeout(() => {
                root.classList.remove('is-nav-animating');
                transitionTimer = 0;
            }, 300);
        }

        root.classList.toggle('is-open', open);
        button.setAttribute('aria-expanded', String(open));
        nav.toggleAttribute('data-lenis-prevent', open);
    };

    button.addEventListener('click', () => {
        const open = !root.classList.contains('is-open');
        setOpen(open);
    });

    nav.addEventListener('click', (event) => {
        if (event.target instanceof Element && event.target.closest('a')) {
            setOpen(false);
        }
    });

    root.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            setOpen(false);
            button.focus();
        }
    });

    const updateViewport = (matches) => {
        if (!matches) setOpen(false, false);
    };

    const onViewportChange = (event) => {
        updateViewport(event.matches);
    };

    if (mobileMedia.addEventListener) {
        mobileMedia.addEventListener('change', onViewportChange);
    } else {
        mobileMedia.addListener(onViewportChange);
    }

    updateViewport(mobileMedia.matches);
}

function HeaderScroll(root, {
    hideOnScroll = false,
    compactOnScroll = false,
    threshold = 0,
} = {}) {
    const scrollThreshold = Math.max(0, Number(threshold) || 0);
    let scrollFrame = 0;
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

    window.addEventListener('scroll', onScroll, { passive: true });
    root.addEventListener('focusin', () => {
        root.classList.remove('is-hidden');
    });
    update();

    const enableTransitions = () => {
        requestAnimationFrame(() => {
            requestAnimationFrame(() => {
                root.classList.add('is-scroll-ready');
            });
        });
    };

    if (document.readyState === 'complete') {
        enableTransitions();
    } else {
        window.addEventListener('pageshow', enableTransitions, { once: true });
    }
}

export default function HeaderNav() {
    const headerNav = document.getElementById("header-nav");

    if (!headerNav) return;

    Navigation(headerNav);
    HeaderScroll(headerNav, {
        hideOnScroll: false,
        compactOnScroll: true,
        threshold: 100,
    });
}
