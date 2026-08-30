class Tabs {
    constructor(root) {
        if (!(root instanceof HTMLElement)) return;
        if (root.__tabsInstance) return root.__tabsInstance;

        const tablist = root.querySelector(":scope > [role=tablist]");
        if (!tablist) return;

        const tabs = Array.from(tablist.querySelectorAll(":scope > [role=tab]"));
        const entries = tabs.map(tab => {
            const panelId = tab.getAttribute("aria-controls");
            const panel = panelId ? root.querySelector(`#${CSS.escape(panelId)}`) : null;
            return panel?.getAttribute("role") === "tabpanel" ? { tab, panel } : null;
        }).filter(Boolean);
        const enabledEntries = entries.filter(({ tab }) => !tab.disabled);
        if (entries.length === 0 || enabledEntries.length === 0) return;

        this.root = root;
        this.tablist = tablist;
        this.entries = entries;
        this.enabledEntries = enabledEntries;
        this.initialState = entries.map(({ tab, panel }) => ({
            tab,
            panel,
            selected: tab.getAttribute("aria-selected"),
            tabindex: tab.getAttribute("tabindex"),
            hidden: panel.hidden,
        }));
        this.controller = new AbortController();
        const { signal } = this.controller;

        tablist.addEventListener("click", event => {
            const tab = event.target.closest?.("[role=tab]");
            if (!tab || tab.disabled || tab.parentElement !== tablist) return;
            this.select(tab);
        }, { signal });
        tablist.addEventListener("keydown", event => this.onKeyDown(event), { signal });

        const selected = enabledEntries.find(({ tab }) => tab.getAttribute("aria-selected") === "true")
            ?? enabledEntries[0];
        this.select(selected.tab, false);
        root.__tabsInstance = this;
    }

    select(selected, focus = true) {
        if (!this.enabledEntries.some(({ tab }) => tab === selected)) return;

        this.entries.forEach(({ tab, panel }) => {
            const active = tab === selected;
            tab.setAttribute("aria-selected", String(active));
            tab.tabIndex = active ? 0 : -1;
            panel.hidden = !active;
        });
        if (focus) selected.focus();
    }

    onKeyDown(event) {
        const tab = event.target.closest?.("[role=tab]");
        if (!tab || tab.disabled || tab.parentElement !== this.tablist) return;

        const tabs = this.enabledEntries.map(entry => entry.tab);
        const index = tabs.indexOf(tab);
        if (index < 0) return;

        const vertical = this.tablist.getAttribute("aria-orientation") === "vertical";
        let destination = null;
        if ((!vertical && event.key === "ArrowLeft") || (vertical && event.key === "ArrowUp")) {
            destination = (index - 1 + tabs.length) % tabs.length;
        }
        if ((!vertical && event.key === "ArrowRight") || (vertical && event.key === "ArrowDown")) {
            destination = (index + 1) % tabs.length;
        }
        if (event.key === "Home") destination = 0;
        if (event.key === "End") destination = tabs.length - 1;
        if (destination === null) return;

        event.preventDefault();
        this.select(tabs[destination]);
    }

    destroy() {
        this.controller?.abort();
        this.initialState?.forEach(({ tab, panel, selected, tabindex, hidden }) => {
            if (selected === null) tab.removeAttribute("aria-selected");
            else tab.setAttribute("aria-selected", selected);
            if (tabindex === null) tab.removeAttribute("tabindex");
            else tab.setAttribute("tabindex", tabindex);
            panel.hidden = hidden;
        });
        if (this.root) delete this.root.__tabsInstance;
    }
}

export { Tabs };

export default function initTabs(root) {
    const instance = new Tabs(root);
    if (!instance?.root) return;
    return () => instance.destroy();
}
