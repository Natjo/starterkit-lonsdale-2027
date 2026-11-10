const FEEDBACK_DURATION = 1800;

export default function initStyleguide(root) {
    const tokenValues = root.querySelectorAll('[data-styleguide-token], [data-styleguide-font-name]');
    const layoutGrids = root.querySelectorAll('[data-styleguide-layout]');
    const breakpointRows = root.querySelectorAll('[data-breakpoint-min]');
    const viewportValues = root.querySelectorAll('[data-styleguide-viewport]');
    const breakpointIndicators = [...root.querySelectorAll('[data-styleguide-breakpoint]')].map(node => {
        let points;
        try { points = JSON.parse(node.dataset.breakpoints); } catch { points = []; }
        const values = Array.isArray(points) ? points.filter(point => typeof point.name === 'string' && Number.isFinite(point.width)).sort((a, b) => a.width - b.width) : [];
        return { node, points: values };
    });
    const hasResponsiveSamples = tokenValues.length || layoutGrids.length || breakpointRows.length || viewportValues.length || breakpointIndicators.length;
    const updateTokenValues = () => {
        const styles = getComputedStyle(document.documentElement);
        viewportValues.forEach(node => { node.textContent = `${window.innerWidth} px`; });
        breakpointIndicators.forEach(({ node, points }) => {
            if (!points.length) return;
            const index = points.findLastIndex(point => window.innerWidth >= point.width);
            const current = points[index];
            const next = points[index + 1];
            node.querySelector('[data-breakpoint-name]').textContent = current ? current.name : `Sous ${points[0].name}`;
            node.querySelector('[data-breakpoint-range]').textContent = !current
                ? `< ${points[0].width} px`
                : next ? `${current.width}–${next.width - 1} px` : `≥ ${current.width} px`;
            node.hidden = false;
        });
        breakpointRows.forEach(row => {
            const min = Number(row.dataset.breakpointMin);
            const max = row.dataset.breakpointMax === undefined ? Infinity : Number(row.dataset.breakpointMax);
            row.classList.toggle('is-active', window.innerWidth >= min && window.innerWidth <= max);
        });
        tokenValues.forEach(node => {
            const value = styles.getPropertyValue(node.dataset.styleguideToken || node.dataset.styleguideFontName).trim();
            node.textContent = node.dataset.styleguideFontName
                ? value.split(',')[0].trim().replace(/^['"]|['"]$/g, '')
                : value;
        });
        const layout = styles.getPropertyValue('--layout');
        const lines = new Set([...layout.matchAll(/\[([^\]]+)\]/g)].flatMap(match => match[1].trim().split(/\s+/)));
        layoutGrids.forEach(grid => {
            let row = 0;
            grid.querySelectorAll('[data-layout-rail]').forEach(rail => {
                const name = rail.dataset.layoutRail;
                const start = lines.has(name) ? name : `${name}-start`;
                const end = rail.dataset.layoutEnd || `${name}-end`;
                rail.hidden = !lines.has(start) || !lines.has(end);
                const guide = rail.dataset.layoutEnd ? null : grid.querySelector(`[data-layout-guide="${name}"]`);
                if (guide) {
                    guide.hidden = rail.hidden;
                    guide.style.gridColumn = `${start} / ${name}-end`;
                }
                if (rail.hidden) return;
                const placement = ['full', 'fluid', 'ctr'].includes(name) && !rail.dataset.layoutEnd ? name : `${start} / ${end}`;
                rail.style.gridColumn = placement;
                rail.style.gridRow = String(++row);
                rail.querySelector('[data-layout-placement]').textContent = `grid-column: ${placement}`;
                rail.querySelector('[data-layout-width]').textContent = `${Math.round(rail.getBoundingClientRect().width)} px`;
            });
        });
    };
    const tokenController = new AbortController();
    if (hasResponsiveSamples) {
        updateTokenValues();
        window.addEventListener('resize', updateTokenValues, { signal: tokenController.signal });
    }
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
    const onAnchorClick = event => {
        if (event.defaultPrevented || event.button !== 0 || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
        const link = event.target instanceof Element ? event.target.closest('a[href^="#"]') : null;
        if (!link || !root.contains(link) || link.hasAttribute('download') || (link.target && link.target !== '_self')) return;
        let id;
        try { id = decodeURIComponent(link.hash.slice(1)); } catch { return; }
        const target = id ? document.getElementById(id) : null;
        if (!target || !root.contains(target)) return;

        event.preventDefault();
        const offset = parseFloat(getComputedStyle(root).getPropertyValue('--styleguide-demo-top')) || 0;
        const top = window.scrollY + target.getBoundingClientRect().top - offset;
        if (window.lenis?.scrollTo) {
            window.lenis.scrollTo(top, { duration: .8, immediate: reducedMotion.matches });
        } else {
            window.scrollTo({ top, behavior: reducedMotion.matches ? 'auto' : 'smooth' });
        }
        if (window.location.hash !== link.hash) window.history.pushState(window.history.state, '', link.hash);
    };
    root.addEventListener('click', onAnchorClick, { signal: tokenController.signal });
    const nav = root.querySelector('.styleguide-nav');
    const navEntries = nav ? [...nav.querySelectorAll('a[href^="#"]')].flatMap(link => {
        let id;
        try { id = decodeURIComponent(link.hash.slice(1)); } catch { return []; }
        const section = document.getElementById(id);
        return section && root.contains(section) ? [{ link, section }] : [];
    }) : [];
    let activeLink = null;
    let activeFrame = null;
    const updateActiveSection = () => {
        activeFrame = null;
        const line = parseFloat(getComputedStyle(root).getPropertyValue('--styleguide-demo-top')) || 0;
        let current = null;
        let closestTop = -Infinity;
        navEntries.forEach(entry => {
            const top = entry.section.getBoundingClientRect().top;
            if (top <= line + 1 && top > closestTop) {
                current = entry.link;
                closestTop = top;
            }
        });
        if (current === activeLink) return;
        activeLink?.removeAttribute('aria-current');
        activeLink = current;
        if (!activeLink) return;
        activeLink.setAttribute('aria-current', 'location');
        const list = nav.querySelector('ul');
        if (list && list.scrollWidth > list.clientWidth) {
            const linkRect = activeLink.getBoundingClientRect();
            const listRect = list.getBoundingClientRect();
            if (linkRect.left < listRect.left || linkRect.right > listRect.right) {
                list.scrollTo({ left: list.scrollLeft + linkRect.left - listRect.left - (list.clientWidth - linkRect.width) / 2, behavior: reducedMotion.matches ? 'auto' : 'smooth' });
            }
        }
    };
    const scheduleActiveSection = () => {
        if (activeFrame === null) activeFrame = requestAnimationFrame(updateActiveSection);
    };
    let sectionObserver;
    if (navEntries.length) {
        window.addEventListener('scroll', scheduleActiveSection, { passive: true, signal: tokenController.signal });
        window.addEventListener('resize', scheduleActiveSection, { signal: tokenController.signal });
        window.addEventListener('hashchange', scheduleActiveSection, { signal: tokenController.signal });
        sectionObserver = new ResizeObserver(scheduleActiveSection);
        sectionObserver.observe(root);
        scheduleActiveSection();
    }
    let navObserver;
    if (nav) {
        const updateStickyOffset = () => {
            const top = parseFloat(getComputedStyle(nav).top) || 0;
            root.style.setProperty('--styleguide-demo-top', `${nav.offsetHeight + top + 16}px`);
            if (navEntries.length) scheduleActiveSection();
        };
        updateStickyOffset();
        navObserver = new ResizeObserver(updateStickyOffset);
        navObserver.observe(nav);
    }
    const clearStickyOffset = () => {
        tokenController.abort();
        navObserver?.disconnect();
        sectionObserver?.disconnect();
        if (activeFrame !== null) cancelAnimationFrame(activeFrame);
        activeLink?.removeAttribute('aria-current');
        if (nav) root.style.removeProperty('--styleguide-demo-top');
    };
    if (!navigator.clipboard?.writeText) return clearStickyOffset;

    const controller = new AbortController();
    const blocks = [];
    const timers = new Map();
    const pending = new Set();
    let destroyed = false;

    root.querySelectorAll('.styleguide-readme pre > code').forEach(code => {
        const pre = code.parentElement;
        const wrapper = document.createElement('div');
        wrapper.className = 'styleguide-code';
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'styleguide-copy';
        button.dataset.styleguideCopy = '';
        button.setAttribute('aria-label', 'Copier le code');
        button.title = 'Copier le code';
        button.innerHTML = `<svg class="styleguide-copy-symbol" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true" focusable="false"><rect x="8" y="8" width="12" height="12" rx="2"/><path d="M16 8V4a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h4"/></svg><svg class="styleguide-copy-check" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" focusable="false"><path d="m5 12 4 4L19 6"/></svg>`;
        const status = document.createElement('span');
        status.className = 'styleguide-copy-status';
        status.setAttribute('role', 'status');
        pre.before(wrapper);
        wrapper.append(button, pre, status);
        blocks.push({ wrapper, pre, button, status, code });
    });

    const onClick = async event => {
        const button = event.target instanceof Element
            ? event.target.closest('[data-styleguide-copy]')
            : null;
        const block = blocks.find(item => item.button === button);
        if (!block || pending.has(button)) return;

        pending.add(button);
        button.setAttribute('aria-busy', 'true');
        clearTimeout(timers.get(button));
        block.status.textContent = '';
        let copied = false;
        try {
            await navigator.clipboard.writeText(block.code.textContent || '');
            copied = true;
        } catch {
            // Le refus du presse-papiers est signalé dans le statut accessible.
        }
        if (destroyed) return;
        pending.delete(button);
        button.removeAttribute('aria-busy');
        button.dataset.copyState = copied ? 'success' : 'error';
        const message = copied ? 'Code copié' : 'Copie impossible';
        button.setAttribute('aria-label', message);
        button.title = message;
        block.status.textContent = message;
        timers.set(button, setTimeout(() => {
            delete button.dataset.copyState;
            button.setAttribute('aria-label', 'Copier le code');
            button.title = 'Copier le code';
            block.status.textContent = '';
            timers.delete(button);
        }, FEEDBACK_DURATION));
    };
    root.addEventListener('click', onClick, { signal: controller.signal });

    return () => {
        destroyed = true;
        clearStickyOffset();
        controller.abort();
        timers.forEach(timer => clearTimeout(timer));
        timers.clear();
        pending.clear();
        blocks.forEach(({ wrapper, pre }) => wrapper.replaceWith(pre));
    };
}
