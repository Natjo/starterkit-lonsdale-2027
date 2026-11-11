const MOBILE_MEDIA = '(max-width: 767px)';

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

export default function HeaderNav() {
    const headerNav = document.getElementById("header-nav-centered");

    if (!headerNav || headerNav.dataset.navigationReady) return;
    headerNav.dataset.navigationReady = 'true';
    Navigation(headerNav);
}
