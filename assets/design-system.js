const FEEDBACK_DURATION = 1800;

export default function initDesignSystem(root) {
    const tokenValues = root.querySelectorAll('[data-design-system-token], [data-design-system-font-name]');
    const layoutGrids = root.querySelectorAll('[data-design-system-layout]');
    const breakpointRows = root.querySelectorAll('[data-breakpoint-min]');
    const viewportValues = root.querySelectorAll('[data-design-system-viewport]');
    const breakpointIndicators = [...root.querySelectorAll('[data-design-system-breakpoint]')].map(node => {
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
            const name = current?.name ?? 'below';
            if (node.dataset.breakpoint !== name) node.dataset.breakpoint = name;
            node.querySelector('[data-breakpoint-name]').textContent = current ? current.name : `Sous ${points[0].name}`;
            node.hidden = false;
        });
        breakpointRows.forEach(row => {
            const min = Number(row.dataset.breakpointMin);
            const max = row.dataset.breakpointMax === undefined ? Infinity : Number(row.dataset.breakpointMax);
            row.classList.toggle('is-active', window.innerWidth >= min && window.innerWidth <= max);
        });
        tokenValues.forEach(node => {
            const value = styles.getPropertyValue(node.dataset.designSystemToken || node.dataset.designSystemFontName).trim();
            node.textContent = node.dataset.designSystemFontName
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
    root.querySelectorAll('[data-card-columns]').forEach(input => {
        const section = input.closest('.design-system-section');
        const preview = section?.querySelector('.design-system-cards-preview');
        const output = input.parentElement.querySelector('output');
        if (!preview) return;
        const updateColumns = () => {
            const columns = Math.max(1, Math.min(3, Number(input.value) || 3));
            preview.style.setProperty('--card-columns', String(columns));
            [...preview.children].forEach((item, index) => {
                item.hidden = index >= columns;
            });
            if (output) output.value = String(columns);
        };
        input.addEventListener('input', updateColumns, { signal: tokenController.signal });
        updateColumns();
    });
    document.addEventListener('keydown', event => {
        if (event.key === 'Tab') root.setAttribute('data-keyboard-navigation', '');
    }, { signal: tokenController.signal, capture: true });
    document.addEventListener('pointerdown', () => {
        root.removeAttribute('data-keyboard-navigation');
    }, { signal: tokenController.signal, capture: true });
    const clearParticles = initHeaderParticles(root, tokenController.signal);
    root.querySelector('[data-design-system-refresh]')?.addEventListener('click', () => {
        window.location.reload();
    }, { signal: tokenController.signal });
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
        const offset = parseFloat(getComputedStyle(root).getPropertyValue('--design-system-demo-top')) || 0;
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
    const nav = root.querySelector('.design-system-nav');
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
        if (nav) {
            const stickyTop = parseFloat(getComputedStyle(nav).top) || 0;
            nav.classList.toggle('is-stuck', nav.getBoundingClientRect().top <= stickyTop + 1);
        }
        const line = parseFloat(getComputedStyle(root).getPropertyValue('--design-system-demo-top')) || 0;
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
    const search = nav?.querySelector('[data-design-system-search]');
    const emptyResults = root.querySelector('[data-design-system-empty]');
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
        search.value = new URL(window.location.href).searchParams.get('design_system_search') ?? search.value;
        search.addEventListener('input', () => {
            cancelInitialRestore();
            filterSections();
            const url = new URL(window.location.href);
            if (search.value.trim()) url.searchParams.set('design_system_search', search.value);
            else url.searchParams.delete('design_system_search');
            // Retain unrelated query parameters and the selected anchor.
            window.history.replaceState(window.history.state, '', url);
        }, { signal: tokenController.signal });
        filterSections();
    }
    let sectionObserver;
    if (nav) {
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
            root.style.setProperty('--design-system-demo-top', `${nav.offsetHeight + top + 32}px`);
            if (navEntries.length) scheduleActiveSection();
        };
        updateStickyOffset();
        navObserver = new ResizeObserver(updateStickyOffset);
        navObserver.observe(nav);
        if (navList) navObserver.observe(navList);
    }
    const clearStickyOffset = () => {
        tokenController.abort();
        root.removeAttribute('data-keyboard-navigation');
        clearParticles();
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
        if (nav) {
            root.style.removeProperty('--design-system-demo-top');
            nav.classList.remove('is-stuck');
        }
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

    root.querySelectorAll('.design-system-readme pre > code').forEach(code => {
        const pre = code.parentElement;
        const wrapper = document.createElement('div');
        wrapper.className = 'design-system-code';
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'design-system-copy';
        button.dataset.designSystemCopy = '';
        button.setAttribute('aria-label', 'Copier le code');
        button.title = 'Copier le code';
        button.innerHTML = `<svg class="design-system-copy-symbol" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true" focusable="false"><rect x="8" y="8" width="12" height="12" rx="2"/><path d="M16 8V4a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h4"/></svg><svg class="design-system-copy-check" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" focusable="false"><path d="m5 12 4 4L19 6"/></svg>`;
        const status = document.createElement('span');
        status.className = 'design-system-copy-status';
        status.setAttribute('role', 'status');
        pre.before(wrapper);
        wrapper.append(button, pre, status);
        blocks.push({ wrapper, pre, button, status, code });
    });

    const onClick = async event => {
        const button = event.target instanceof Element
            ? event.target.closest('[data-design-system-copy]')
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
    const states = new Map([...root.querySelectorAll('[data-design-system-strate-options]')].map(form => {
        const target = form.closest('.design-system-section')?.querySelector('.design-system-strate-preview > .strate');
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
    const buttons = new Map([...root.querySelectorAll('[data-design-system-panel-toggle]')].flatMap(button => {
        const panel = root.ownerDocument.getElementById(button.getAttribute('aria-controls'));
        return panel && root.contains(panel) && panel.hasAttribute('data-design-system-panel') ? [[button, panel]] : [];
    }));
    if (!buttons.size) return () => {};
    let active = null;
    const setOpeningHeight = panel => {
        if (panel.dataset.designSystemPanel !== 'readme') return;
        const viewport = window.visualViewport;
        const bottom = viewport ? viewport.height + viewport.offsetTop : window.innerHeight;
        const room = Math.max(0, Math.floor(bottom - panel.getBoundingClientRect().top - 16));
        panel.style.setProperty('--design-system-panel-height', `${room}px`);
    };
    const close = (restoreFocus = false) => {
        if (!active) return;
        const { button, panel } = active;
        panel.style.removeProperty('--design-system-panel-height');
        active = null;
        panel.hidden = true;
        button.setAttribute('aria-expanded', 'false');
        if (restoreFocus && button.isConnected && !button.closest('[hidden]')) button.focus({ preventScroll: true });
    };
    root.addEventListener('click', event => {
        const button = event.target.closest?.('[data-design-system-panel-toggle]');
        if (button && buttons.has(button)) {
            event.preventDefault();
            if (active?.button === button) { close(true); return; }
            close();
            const panel = buttons.get(button);
            active = { button, panel };
            panel.hidden = false;
            button.setAttribute('aria-expanded', 'true');
            const toolbar = button.closest('[data-design-system-strate-tools]');
            if (toolbar && toolbar.getBoundingClientRect().bottom > window.innerHeight - Math.min(320, window.innerHeight / 2)) {
                revealSection(toolbar.closest('.design-system-section'));
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

function initHeaderParticles(root, signal) {
    const canvas = root.querySelector('.design-system-particles');
    const context = canvas?.getContext('2d');
    if (!context) return () => {};
    const motion = window.matchMedia('(prefers-reduced-motion: reduce)');
    let width = 0;
    let height = 0;
    let points = [];
    let visible = false;
    let frame = null;
    let previous = 0;
    let color;
    const draw = (elapsed = 0) => {
        context.clearRect(0, 0, width, height);
        context.fillStyle = color;
        context.strokeStyle = color;
        context.lineWidth = 1;
        for (const point of points) {
            point.x += point.vx * elapsed;
            point.y += point.vy * elapsed;
            if (point.x < 0 || point.x > width) point.vx *= -1;
            if (point.y < 0 || point.y > height) point.vy *= -1;
            point.x = Math.max(0, Math.min(width, point.x));
            point.y = Math.max(0, Math.min(height, point.y));
        }
        const reach = 150;
        for (let i = 0; i < points.length; i++) {
            const point = points[i];
            for (let j = i + 1; j < points.length; j++) {
                const other = points[j];
                const distance = Math.hypot(point.x - other.x, point.y - other.y);
                if (distance >= reach) continue;
                context.globalAlpha = .42 * (1 - distance / reach);
                context.beginPath();
                context.moveTo(point.x, point.y);
                context.lineTo(other.x, other.y);
                context.stroke();
            }
            context.globalAlpha = .65;
            context.beginPath();
            context.arc(point.x, point.y, point.radius, 0, Math.PI * 2);
            context.fill();
        }
        context.globalAlpha = 1;
    };
    const tick = now => {
        frame = requestAnimationFrame(tick);
        if (now - previous < 1000 / 30) return;
        const elapsed = previous ? Math.min((now - previous) / 1000, .06) : 0;
        previous = now;
        draw(elapsed);
    };
    const update = () => {
        if (frame !== null) cancelAnimationFrame(frame);
        frame = null;
        previous = 0;
        if (visible && !document.hidden && !motion.matches && width && height) frame = requestAnimationFrame(tick);
    };
    const resize = () => {
        const boundary = root.querySelector('.st-components') || root.querySelector('.design-system-intro');
        if (boundary) {
            const rootTop = root.getBoundingClientRect().top;
            const bounds = boundary.getBoundingClientRect();
            canvas.style.height = `${Math.max(0, (boundary.matches('.st-components') ? bounds.top : bounds.bottom) - rootTop)}px`;
        }
        const bounds = canvas.getBoundingClientRect();
        width = bounds.width;
        height = bounds.height;
        const ratio = Math.min(window.devicePixelRatio || 1, 2);
        canvas.width = Math.round(width * ratio);
        canvas.height = Math.round(height * ratio);
        context.setTransform(ratio, 0, 0, ratio, 0, 0);
        color = getComputedStyle(root).getPropertyValue('--sg-accent').trim() || '#315efb';
        points = Array.from({ length: Math.min(65, Math.ceil(width * height / 12000)) }, () => ({
            x: Math.random() * width, y: Math.random() * height,
            vx: (Math.random() - .5) * 12, vy: (Math.random() - .5) * 12,
            radius: .8 + Math.random() * 1.2,
        }));
        draw();
        update();
    };
    const resizeObserver = new ResizeObserver(resize);
    resizeObserver.observe(root);
    const intro = root.querySelector('.design-system-intro');
    if (intro) resizeObserver.observe(intro);
    const observer = new IntersectionObserver(entries => {
        visible = entries[0].isIntersecting;
        update();
    });
    observer.observe(canvas);
    motion.addEventListener('change', update, { signal });
    document.addEventListener('visibilitychange', update, { signal });
    return () => {
        resizeObserver.disconnect();
        observer.disconnect();
        if (frame !== null) cancelAnimationFrame(frame);
    };
}
