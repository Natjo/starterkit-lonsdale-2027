const coordinators = new Map();

const escapeSelector = (value) => {
    if (window.CSS?.escape) return window.CSS.escape(value);
    return value.replace(/[^A-Za-z0-9_-]/g, "\\$&");
};

const getForms = (group) => Array.from(document.querySelectorAll(
    `form[data-filters-group="${escapeSelector(group)}"]`,
));

const buildUrl = (group) => {
    const forms = getForms(group);
    const action = forms[0]?.action || window.location.href;
    const url = new URL(action, window.location.href);
    const params = new URLSearchParams();
    const visibleNames = new Set();
    const appendedEntries = new Set();

    forms.forEach((form) => {
        Array.from(form.elements).forEach((control) => {
            if (control.name && control.type !== "hidden") visibleNames.add(control.name);
        });
    });

    forms.forEach((form) => {
        const formVisibleNames = new Set(
            Array.from(form.elements)
                .filter((control) => control.name && control.type !== "hidden")
                .map((control) => control.name),
        );

        new FormData(form).forEach((value, name) => {
            if (typeof value !== "string" || value === "") return;
            if (visibleNames.has(name) && !formVisibleNames.has(name)) return;

            const entryKey = `${name}\u0000${value}`;
            if (appendedEntries.has(entryKey)) return;

            appendedEntries.add(entryKey);
            params.append(name, value);
        });
    });

    url.search = params.toString();
    return url;
};

const createCoordinator = (group, targetId) => {
    const controller = new AbortController();
    const { signal } = controller;
    let requestController = null;
    let teardownTimer = null;

    const fetchPage = async (url, updateHistory = true) => {
        const target = document.getElementById(targetId);
        if (!target) {
            window.location.assign(url.toString());
            return;
        }

        requestController?.abort();
        requestController = new AbortController();
        target.setAttribute("aria-busy", "true");

        try {
            const response = await fetch(url, {
                headers: { "X-Requested-With": "XMLHttpRequest" },
                signal: requestController.signal,
            });
            if (!response.ok) throw new Error(`HTTP ${response.status}`);

            const page = new DOMParser().parseFromString(await response.text(), "text/html");
            const nextTarget = page.getElementById(targetId);
            if (!nextTarget) throw new Error(`Missing AJAX target: ${targetId}`);

            const currentForms = getForms(group).filter((form) => !target.contains(form));
            const nextForms = Array.from(page.querySelectorAll(
                `form[data-filters-group="${escapeSelector(group)}"]`,
            )).filter((form) => !nextTarget.contains(form));

            currentForms.forEach((form, index) => {
                if (nextForms[index]) form.replaceWith(document.importNode(nextForms[index], true));
            });

            target.innerHTML = nextTarget.innerHTML;
            target.removeAttribute("aria-busy");
            document.title = page.title || document.title;

            if (updateHistory) window.history.pushState({}, "", url);
            target.dispatchEvent(new CustomEvent("filters:updated", {
                bubbles: true,
                detail: { url: url.toString() },
            }));
        } catch (error) {
            if (error?.name === "AbortError") return;
            window.location.assign(url.toString());
        }
    };

    document.addEventListener("submit", (event) => {
        const form = event.target.closest?.(`form[data-filters-group="${escapeSelector(group)}"]`);
        if (!form) return;

        event.preventDefault();
        fetchPage(buildUrl(group));
    }, { signal });

    document.addEventListener("change", (event) => {
        const form = event.target.closest?.(`form[data-filters-group="${escapeSelector(group)}"]`);
        if (!form || form.dataset.filtersSubmitOnChange !== "true") return;

        fetchPage(buildUrl(group));
    }, { signal });

    document.addEventListener("click", (event) => {
        const reset = event.target.closest?.(
            `form[data-filters-group="${escapeSelector(group)}"] .filters-reset[href]`,
        );
        if (reset) {
            event.preventDefault();
            fetchPage(new URL(reset.href, window.location.href));
            return;
        }

        const link = event.target.closest?.(`#${escapeSelector(targetId)} .pagination a[href]`);
        if (!link) return;

        event.preventDefault();
        fetchPage(new URL(link.href, window.location.href));
    }, { signal });

    window.addEventListener("popstate", () => {
        fetchPage(new URL(window.location.href), false);
    }, { signal });

    return {
        forms: new Set(),
        add(form) {
            if (teardownTimer) window.clearTimeout(teardownTimer);
            teardownTimer = null;
            this.forms.add(form);
        },
        remove(form) {
            this.forms.delete(form);
            if (this.forms.size > 0) return;

            teardownTimer = window.setTimeout(() => {
                if (this.forms.size > 0) return;
                requestController?.abort();
                controller.abort();
                coordinators.delete(group);
            }, 0);
        },
    };
};

export default function initFilters(root) {
    const group = root.dataset.filtersGroup?.trim();
    const targetId = root.dataset.filtersTarget?.trim();
    if (!group || !targetId) return undefined;

    let coordinator = coordinators.get(group);
    if (!coordinator) {
        coordinator = createCoordinator(group, targetId);
        coordinators.set(group, coordinator);
    }

    coordinator.add(root);
    return () => coordinator.remove(root);
}
