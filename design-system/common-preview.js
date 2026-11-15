/** Update only the requested region. The starter hydrator owns replacement headers. */
export default function initCommonPreview(form) {
    const demo = document.querySelector('[data-common-demo]');
    if (!demo) return () => {};
    // Partial renders restart PHP's ID counter. Scope the footer search locally.
    const scopeFooterSearch = region => {
        region?.querySelectorAll('.footer-search').forEach(form => {
            const input = form.querySelector('input[type="search"]');
            const label = form.querySelector('label');
            if (!input || !label) return;
            input.id = 'forge-footer-search-field';
            label.htmlFor = input.id;
        });
    };
    scopeFooterSearch(demo.querySelector('[data-common-footer-slot]'));
    const events = new AbortController();
    const requests = new Map();
    const feedback = form.querySelector('[data-common-feedback]');
    const moduleNames = new Map([
        ['header-nav', 'common/header-nav'],
        ['header-nav-dropdown', 'common/header-nav-dropdown'],
        ['header-nav-mega', 'common/header-nav-mega'],
    ]);
    const updateDescription = () => {
        const description = form.querySelector('[data-common-navigation-description]');
        const select = form.querySelector('[name="header_navigation"]');
        if (description && select) description.textContent = select.selectedOptions[0]?.dataset.description ?? '';
    };
    updateDescription();
    const updateFooterOptions = () => {
        const type = form.querySelector('[name="footer_type"]')?.value;
        if (!type) return;
        const description = form.querySelector('[data-common-footer-description]');
        if (description) description.textContent = type === 'simple' ? 'Un footer compact avec ses liens sur un niveau.' : 'Jusqu’à trois colonnes : titres au premier niveau, liens au second.';
        form.querySelectorAll('[data-footer-option]').forEach(label => {
            const allowed = !(['text','newsletter','search'].includes(label.dataset.footerOption) && type === 'simple');
            label.hidden = !allowed;
            const input = label.querySelector('input');
            input.disabled = !allowed;
            if (!allowed) input.checked = false;
        });
    };
    updateFooterOptions();
    const selectionUrl = () => {
        const url = new URL(form.action, location.href);
        url.search = new URLSearchParams(new FormData(form)).toString();
        url.hash = location.hash;
        return url;
    };
    const update = async (region) => {
        requests.get(region)?.abort();
        const request = new AbortController();
        requests.set(region, request);
        const url = selectionUrl();
        url.searchParams.set('common_fragment', region);
        feedback.hidden = true;
        demo.setAttribute('aria-busy', 'true');
        try {
            const response = await fetch(url, { signal: request.signal, credentials: 'same-origin', cache: 'no-store' });
            if (!response.ok) throw new Error('Aperçu indisponible.');
            const parsed = new DOMParser().parseFromString(await response.text(), 'text/html');
            if (request.signal.aborted || requests.get(region) !== request) return;
            const selector = region === 'header' ? 'header[id^="header-nav"]' : '[data-common-footer-slot]';
            const source = parsed.querySelector(selector);
            const previous = demo.querySelector(':scope > ' + selector);
            if (!source || !previous || (region === 'header' && !moduleNames.has(source.id))) throw new Error('Aperçu incomplet.');
            const next = document.importNode(source, true);
            if (region === 'footer') scopeFooterSearch(next);
            const scroll = { left: window.scrollX, top: window.scrollY };
            if (region === 'header') {
                next.dataset.module = moduleNames.get(next.id);
                previous.dispatchEvent(new Event('theme:header-destroy'));
            }
            previous.replaceWith(next);
            history.replaceState(null, '', selectionUrl());
            window.lenis?.resize();
            // Native scroll anchoring must not move the configuration being edited.
            if (window.scrollY !== scroll.top || window.scrollX !== scroll.left) window.scrollTo(scroll);
        } catch (error) {
            if (request.signal.aborted) return;
            feedback.textContent = 'Impossible de mettre à jour l’aperçu. Réessayez en modifiant une option.';
            feedback.hidden = false;
        } finally {
            if (requests.get(region) === request) requests.delete(region);
            if (!requests.size) demo.removeAttribute('aria-busy');
        }
    };
    form.querySelector('[data-common-apply]').hidden = true;
    form.addEventListener('change', (event) => {
        if (!event.target.name) return;
        if (event.target.name === 'header_navigation') updateDescription();
        if (event.target.name === 'footer_type') updateFooterOptions();
        update(event.target.name === 'forge_footer' || event.target.name.startsWith('footer_') ? 'footer' : 'header');
    }, { signal: events.signal });
    form.addEventListener('submit', (event) => {
        event.preventDefault();
        if (form.querySelector('[name="header_navigation"]')) update('header');
        if (form.querySelector('[name="footer_type"], [name="forge_footer"]:not(:disabled)')) update('footer');
    }, { signal: events.signal });
    return () => {
        events.abort();
        requests.forEach(request => request.abort());
        requests.clear();
    };
}
