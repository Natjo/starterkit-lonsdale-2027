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
            const name = current?.name ?? 'below';
            if (node.dataset.breakpoint !== name) node.dataset.breakpoint = name;
            node.querySelector('[data-breakpoint-name]').textContent = current ? `breakpoint-${current.name}` : `Sous breakpoint-${points[0].name}`;
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
    const clearStrateOptions = initStrateOptions(root, tokenController.signal);
    if (hasResponsiveSamples) {
        updateTokenValues();
        window.addEventListener('resize', updateTokenValues, { signal: tokenController.signal });
    }
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
    const initialRestoreController = new AbortController();
    let anchorRestoreFrame = null;
    const cancelInitialRestore = () => {
        initialRestoreController.abort();
        if (anchorRestoreFrame !== null) cancelAnimationFrame(anchorRestoreFrame);
        anchorRestoreFrame = null;
    };
    const anchorTarget = hash => {
        let id;
        try { id = decodeURIComponent(hash.slice(1)); } catch { return null; }
        const target = id ? document.getElementById(id) : null;
        return target && root.contains(target) && !target.closest('[hidden]') ? target : null;
    };
    const scrollToSection = (target, immediate = false) => {
        const offset = parseFloat(getComputedStyle(root).getPropertyValue('--styleguide-demo-top')) || 0;
        const top = Math.max(0, window.scrollY + target.getBoundingClientRect().top - offset);
        if (window.lenis?.scrollTo) {
            window.lenis.scrollTo(top, { duration: .8, immediate: immediate || reducedMotion.matches });
        } else {
            window.scrollTo({ top, behavior: immediate || reducedMotion.matches ? 'instant' : 'smooth' });
        }
    };
    const closeStratePanel = initStratePanels(root, tokenController.signal, section => scrollToSection(section, true));
    const onAnchorClick = event => {
        if (event.defaultPrevented || event.button !== 0 || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
        const link = event.target instanceof Element ? event.target.closest('a[href^="#"]') : null;
        if (!link || !root.contains(link) || link.hasAttribute('download') || (link.target && link.target !== '_self')) return;
        const target = anchorTarget(link.hash);
        if (!target) return;

        event.preventDefault();
        cancelInitialRestore();
        scrollToSection(target);
        if (window.location.hash !== link.hash) window.history.pushState(window.history.state, '', link.hash);
    };
    root.addEventListener('click', onAnchorClick, { signal: tokenController.signal });
    const nav = root.querySelector('.styleguide-nav');
    const navList = nav?.querySelector('ul');
    const updateNavFade = () => {
        if (!navList) return;
        navList.classList.toggle('has-overflow-start', navList.scrollLeft > 1);
        navList.classList.toggle('has-overflow-end', navList.scrollWidth - navList.clientWidth - navList.scrollLeft > 1);
    };
    let navDrag = null;
    let suppressNavClick = false;
    if (navList) {
        const signal = tokenController.signal;
        navList.addEventListener('scroll', updateNavFade, { passive: true, signal });
        navList.addEventListener('pointerdown', event => {
            if (event.pointerType !== 'mouse' || event.button !== 0 || event.ctrlKey || event.metaKey || event.altKey
                || navList.scrollWidth <= navList.clientWidth) return;
            suppressNavClick = false;
            navDrag = { id: event.pointerId, x: event.clientX, y: event.clientY, left: navList.scrollLeft, moved: false };
        }, { signal });
        const endNavDrag = event => {
            if (!navDrag || event.pointerId !== navDrag.id) return;
            suppressNavClick = navDrag.moved;
            const id = navDrag.id;
            navDrag = null;
            navList.classList.remove('is-dragging');
            if (navList.hasPointerCapture(id)) navList.releasePointerCapture(id);
        };
        window.addEventListener('pointermove', event => {
            if (!navDrag || event.pointerId !== navDrag.id) return;
            if (event.buttons === 0) return endNavDrag(event);
            const distance = event.clientX - navDrag.x;
            if (!navDrag.moved) {
                if (Math.abs(distance) < 6) return;
                if (Math.abs(event.clientY - navDrag.y) > Math.abs(distance)) { navDrag = null; return; }
                navDrag.moved = true;
                navList.classList.add('is-dragging');
                navList.setPointerCapture(event.pointerId);
            }
            event.preventDefault();
            navList.scrollLeft = navDrag.left - distance;
        }, { signal, passive: false });
        window.addEventListener('pointerup', endNavDrag, { signal });
        window.addEventListener('pointercancel', endNavDrag, { signal });
        navList.addEventListener('lostpointercapture', endNavDrag, { signal });
        navList.addEventListener('dragstart', event => event.preventDefault(), { signal });
        navList.addEventListener('click', event => {
            if (!suppressNavClick || event.detail === 0) return;
            suppressNavClick = false;
            event.preventDefault();
            event.stopPropagation();
        }, { signal, capture: true });
    }
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
            if (entry.section.hidden) return;
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
    const search = nav?.querySelector('[data-styleguide-search]');
    const emptyResults = root.querySelector('[data-styleguide-empty]');
    const searchText = value => value.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
    const searchableEntries = navEntries.map(entry => ({
        ...entry,
        item: entry.link.closest('li'),
        text: searchText(`${entry.link.textContent} ${entry.section.id.replace(/-/g, ' ')}`),
    }));
    const filterSections = () => {
        closeStratePanel();
        const terms = searchText(search.value.trim()).split(/\s+/).filter(Boolean);
        let visible = 0;
        searchableEntries.forEach(entry => {
            const matches = terms.every(term => entry.text.includes(term));
            entry.section.hidden = !matches;
            if (entry.item) entry.item.hidden = !matches;
            if (matches) visible++;
        });
        if (emptyResults) emptyResults.hidden = visible > 0;
        updateNavFade();
        scheduleActiveSection();
    };
    if (search) {
        search.value = new URL(window.location.href).searchParams.get('styleguide_search') ?? search.value;
        search.addEventListener('input', () => {
            cancelInitialRestore();
            filterSections();
            const url = new URL(window.location.href);
            if (search.value.trim()) url.searchParams.set('styleguide_search', search.value);
            else url.searchParams.delete('styleguide_search');
            // Retain unrelated query parameters and the selected anchor.
            window.history.replaceState(window.history.state, '', url);
        }, { signal: tokenController.signal });
        filterSections();
    }
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
            updateNavFade();
            const top = parseFloat(getComputedStyle(nav).top) || 0;
            root.style.setProperty('--styleguide-demo-top', `${nav.offsetHeight + top + 32}px`);
            if (navEntries.length) scheduleActiveSection();
        };
        updateStickyOffset();
        navObserver = new ResizeObserver(updateStickyOffset);
        navObserver.observe(nav);
        if (navList) navObserver.observe(navList);
    }
    const clearStickyOffset = () => {
        tokenController.abort();
        closeStratePanel();
        clearStrateOptions();
        cancelInitialRestore();
        if (search) {
            search.value = '';
            searchableEntries.forEach(entry => {
                entry.section.hidden = false;
                if (entry.item) entry.item.hidden = false;
            });
            if (emptyResults) emptyResults.hidden = true;
        }
        if (navDrag && navList?.hasPointerCapture(navDrag.id)) navList.releasePointerCapture(navDrag.id);
        navDrag = null;
        navList?.classList.remove('is-dragging', 'has-overflow-start', 'has-overflow-end');
        navObserver?.disconnect();
        sectionObserver?.disconnect();
        if (activeFrame !== null) cancelAnimationFrame(activeFrame);
        activeLink?.removeAttribute('aria-current');
        if (nav) root.style.removeProperty('--styleguide-demo-top');
    };
    // Restore after filtering, stylesheet/image loading and font metrics have settled.
    const initialHash = window.location.hash;
    if (initialHash && anchorTarget(initialHash)) {
        const { signal } = initialRestoreController;
        for (const type of ['pointerdown', 'wheel', 'keydown']) {
            window.addEventListener(type, cancelInitialRestore, { signal, passive: true });
        }
        const restoreAnchor = () => {
            Promise.resolve(document.fonts?.ready).then(() => {
                if (signal.aborted) return;
                anchorRestoreFrame = requestAnimationFrame(() => {
                    anchorRestoreFrame = null;
                    const target = anchorTarget(initialHash);
                    if (!signal.aborted && window.location.hash === initialHash && target) scrollToSection(target, true);
                    cancelInitialRestore();
                });
            });
        };
        if (document.readyState === 'complete') restoreAnchor();
        else window.addEventListener('load', restoreAnchor, { once: true, signal });
    }
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

/** Local controls for the common options, without replacing hydrated markup. */
function initStrateOptions(root, signal) {
    const managedClass = /^(?:ctr-|mt-|mb-|bg-|pt-|pb-)/;
    const states = new Map([...root.querySelectorAll('[data-styleguide-strate-options]')].map(form => {
        const target = form.closest('.styleguide-section')?.querySelector('.styleguide-strate-preview > .strate');
        const controls = new Map([...form.querySelectorAll('[data-strate-option]')].map(control => [control.dataset.strateOption, control]));
        const originalClasses = target ? [...target.classList].filter(name => managedClass.test(name)) : [];
        return [form, { form, target, controls, originalClasses, currentClasses: originalClasses, message: form.querySelector('[data-strate-options-message]') }];
    }));
    if (!states.size) return () => {};
    const read = (state, name) => {
        const control = state.controls.get(name);
        return control?.type === 'checkbox' ? control.checked : control?.value ?? '';
    };
    const syncControls = state => {
        const background = read(state, 'background.hasbackground');
        state.controls.forEach(control => {
            control.disabled = !state.target || (control.hasAttribute('data-strate-background') && !background);
            control.closest('label').classList.toggle('is-disabled', control.disabled);
        });
        state.form.querySelector('[type="reset"]').disabled = !state.target;
        if (!state.target) state.message.textContent = 'Cet exemple ne contient pas de strate à modifier.';
    };
    const apply = state => {
        if (!state.target) return;
        const classes = [];
        const add = (prefix, value, omitted = '') => {
            if (value !== '' && value !== omitted) classes.push(prefix + value);
        };
        add('ctr-', read(state, 'container'));
        add('mt-', read(state, 'margin.top'));
        add('mb-', read(state, 'margin.bottom') || '0', 'md');
        if (read(state, 'background.hasbackground')) {
            add('bg-', read(state, 'background.color'));
            add('pt-', read(state, 'background.padding.top'), 'md');
            add('pb-', read(state, 'background.padding.bottom'), 'md');
        }
        state.target.classList.remove(...state.currentClasses);
        state.target.classList.add(...classes);
        state.currentClasses = classes;

        syncControls(state);
    };
    const restore = state => {
        if (!state.target) return;
        state.target.classList.remove(...state.currentClasses);
        state.target.classList.add(...state.originalClasses);
        state.currentClasses = state.originalClasses;
        state.message.textContent = '';
    };
    states.forEach(syncControls);
    root.addEventListener('input', event => {
        const control = event.target.closest?.('[data-strate-option]');
        const state = control ? states.get(control.closest('form')) : null;
        if (state) apply(state);
    }, { signal });
    root.addEventListener('submit', event => {
        if (states.has(event.target)) event.preventDefault();
    }, { signal });
    root.addEventListener('reset', event => {
        const state = states.get(event.target);
        if (!state) return;
        queueMicrotask(() => {
            if (signal.aborted || event.defaultPrevented) return;
            restore(state);
            syncControls(state);
        });
    }, { signal });
    return () => states.forEach(state => {
        state.form.reset();
        restore(state);
        syncControls(state);
    });
}

/** Nonmodal panels over a strate; keep the example and its modules intact. */
function initStratePanels(root, signal, revealSection) {
    const buttons = new Map([...root.querySelectorAll('[data-styleguide-panel-toggle]')].flatMap(button => {
        const panel = root.ownerDocument.getElementById(button.getAttribute('aria-controls'));
        return panel && root.contains(panel) && panel.hasAttribute('data-styleguide-panel') ? [[button, panel]] : [];
    }));
    if (!buttons.size) return () => {};
    let active = null;
    const setOpeningHeight = panel => {
        if (panel.dataset.styleguidePanel !== 'readme') return;
        const viewport = window.visualViewport;
        const bottom = viewport ? viewport.height + viewport.offsetTop : window.innerHeight;
        const room = Math.max(0, Math.floor(bottom - panel.getBoundingClientRect().top - 16));
        panel.style.setProperty('--styleguide-panel-height', `${room}px`);
    };
    const close = (restoreFocus = false) => {
        if (!active) return;
        const { button, panel } = active;
        panel.style.removeProperty('--styleguide-panel-height');
        active = null;
        panel.hidden = true;
        button.setAttribute('aria-expanded', 'false');
        if (restoreFocus && button.isConnected && !button.closest('[hidden]')) button.focus({ preventScroll: true });
    };
    root.addEventListener('click', event => {
        const button = event.target.closest?.('[data-styleguide-panel-toggle]');
        if (button && buttons.has(button)) {
            event.preventDefault();
            if (active?.button === button) { close(true); return; }
            close();
            const panel = buttons.get(button);
            active = { button, panel };
            panel.hidden = false;
            button.setAttribute('aria-expanded', 'true');
            const toolbar = button.closest('[data-styleguide-strate-tools]');
            if (toolbar && toolbar.getBoundingClientRect().bottom > window.innerHeight - Math.min(320, window.innerHeight / 2)) {
                revealSection(toolbar.closest('.styleguide-section'));
            }
            setOpeningHeight(panel);
            const control = panel.querySelector('input:not(:disabled), select:not(:disabled)');
            (control || panel).focus({ preventScroll: true });
        }
    }, { signal });
    root.addEventListener('keydown', event => {
        if (event.key !== 'Escape' || event.defaultPrevented || !active) return;
        event.preventDefault();
        event.stopPropagation();
        close(true);
    }, { signal });
    root.ownerDocument.addEventListener('pointerdown', event => {
        if (active && !active.panel.contains(event.target) && !active.button.contains(event.target)) close();
    }, { signal });
    return close;
}
