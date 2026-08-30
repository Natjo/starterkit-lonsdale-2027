const TYPEAHEAD_DELAY = 500;

export default function initSelectCustom(root) {
    if (!(root instanceof HTMLElement)) return;
    if (root.__selectCustomInstance) return root.__selectCustomInstance.destroy;

    const select = root.querySelector("[data-select-native]");
    const label = root.querySelector("label[for]");
    const ui = root.querySelector("[data-select-ui]");
    const button = root.querySelector("[data-select-button]");
    const buttonLabel = root.querySelector("[data-select-button-label]");
    const listbox = root.querySelector("[data-select-list]");
    const options = Array.from(root.querySelectorAll("[data-select-option]"));
    if (!select || !ui || !button || !buttonLabel || !listbox || options.length === 0) return;

    const controller = new AbortController();
    const { signal } = controller;
    const optionByIndex = new Map(options.map(option => [Number(option.dataset.index), option]));
    const previousTabindex = select.getAttribute("tabindex");
    const previousAriaHidden = select.getAttribute("aria-hidden");
    const previousLabelFor = label?.getAttribute("for") ?? null;
    const placeholder = root.dataset.placeholder || "";
    let outsideController = null;
    let activeIndex = -1;
    let typeahead = "";
    let typeaheadTimer = 0;
    let resetFrame = 0;

    const isOpen = () => button.getAttribute("aria-expanded") === "true";
    const enabledIndices = () => options
        .map(option => Number(option.dataset.index))
        .filter(index => !select.options[index]?.disabled);

    const clearActive = () => {
        activeIndex = -1;
        options.forEach(option => delete option.dataset.active);
        button.removeAttribute("aria-activedescendant");
    };

    const setActive = (index, scroll = true) => {
        const option = optionByIndex.get(index);
        const nativeOption = select.options[index];
        if (!option || !nativeOption || nativeOption.disabled) return;

        activeIndex = index;
        options.forEach(item => item.toggleAttribute("data-active", item === option));
        button.setAttribute("aria-activedescendant", option.id);
        if (scroll && isOpen()) option.scrollIntoView({ block: "nearest" });
    };

    const selectedIndices = () => Array.from(select.options)
        .map((option, index) => option.selected && option.value !== "" ? index : -1)
        .filter(index => index >= 0 && optionByIndex.has(index));

    const syncFromNative = () => {
        const selected = selectedIndices();
        options.forEach(option => {
            const nativeOption = select.options[Number(option.dataset.index)];
            option.setAttribute("aria-selected", nativeOption?.selected ? "true" : "false");
        });

        const labels = selected
            .map(index => select.options[index]?.textContent?.trim() || "")
            .filter(Boolean);
        buttonLabel.textContent = labels.length > 0 ? labels.join(", ") : placeholder;
        button.disabled = select.disabled;
        button.setAttribute("aria-required", select.required ? "true" : "false");

        if (select.validity.valid) {
            root.removeAttribute("data-invalid");
            button.removeAttribute("aria-invalid");
        }
    };

    const close = () => {
        outsideController?.abort();
        outsideController = null;
        button.setAttribute("aria-expanded", "false");
        listbox.hidden = true;
        clearActive();
    };

    const open = (fallback = "first") => {
        if (button.disabled || isOpen()) return;

        button.setAttribute("aria-expanded", "true");
        listbox.hidden = false;
        const enabled = enabledIndices();
        const selected = selectedIndices().find(index => enabled.includes(index));
        const fallbackIndex = fallback === "last" ? enabled.at(-1) : enabled[0];
        if (selected !== undefined) setActive(selected, false);
        else if (fallbackIndex !== undefined) setActive(fallbackIndex, false);

        outsideController = new AbortController();
        document.addEventListener("pointerdown", event => {
            if (!root.contains(event.target)) close();
        }, { signal: outsideController.signal });
    };

    const emitChange = () => {
        syncFromNative();
        select.dispatchEvent(new Event("input", { bubbles: true }));
        select.dispatchEvent(new Event("change", { bubbles: true }));
    };

    const commit = (index) => {
        const nativeOption = select.options[index];
        if (!nativeOption || nativeOption.disabled) return;

        if (select.multiple) nativeOption.selected = !nativeOption.selected;
        else select.selectedIndex = index;

        emitChange();
        if (select.multiple) setActive(index);
        else close();
    };

    const move = direction => {
        const enabled = enabledIndices();
        if (enabled.length === 0) return;
        const current = enabled.indexOf(activeIndex);
        const start = current < 0 ? (direction > 0 ? -1 : 0) : current;
        const next = (start + direction + enabled.length) % enabled.length;
        setActive(enabled[next]);
    };

    const findByTypeahead = query => {
        const enabled = enabledIndices();
        if (enabled.length === 0) return;
        const normalized = query.toLocaleLowerCase();
        const current = Math.max(enabled.indexOf(activeIndex), -1);
        const ordered = [...enabled.slice(current + 1), ...enabled.slice(0, current + 1)];
        const match = ordered.find(index => (
            select.options[index]?.textContent?.trim().toLocaleLowerCase().startsWith(normalized)
        ));
        if (match !== undefined) setActive(match);
    };

    button.addEventListener("click", () => {
        if (isOpen()) close();
        else open();
    }, { signal });

    button.addEventListener("keydown", event => {
        const { key } = event;

        if (key === "Tab") {
            close();
            return;
        }
        if (key === "Escape") {
            if (!isOpen()) return;
            event.preventDefault();
            close();
            return;
        }
        if (key === "ArrowDown" || key === "ArrowUp") {
            event.preventDefault();
            if (!isOpen()) open(key === "ArrowUp" ? "last" : "first");
            else move(key === "ArrowDown" ? 1 : -1);
            return;
        }
        if (key === "Home" || key === "End") {
            event.preventDefault();
            if (!isOpen()) open(key === "End" ? "last" : "first");
            const enabled = enabledIndices();
            const index = key === "End" ? enabled.at(-1) : enabled[0];
            if (index !== undefined) setActive(index);
            return;
        }
        if (key === "Enter" || key === " " || key === "Spacebar") {
            event.preventDefault();
            if (!isOpen()) open();
            else if (activeIndex >= 0) commit(activeIndex);
            return;
        }
        if (key.length === 1 && !event.altKey && !event.ctrlKey && !event.metaKey) {
            event.preventDefault();
            if (!isOpen()) open();
            window.clearTimeout(typeaheadTimer);
            typeahead += key;
            findByTypeahead(typeahead);
            typeaheadTimer = window.setTimeout(() => { typeahead = ""; }, TYPEAHEAD_DELAY);
        }
    }, { signal });

    listbox.addEventListener("pointerdown", event => event.preventDefault(), { signal });
    listbox.addEventListener("click", event => {
        const option = event.target.closest?.("[data-select-option]");
        if (!option || !listbox.contains(option)) return;
        const index = Number(option.dataset.index);
        setActive(index, false);
        commit(index);
    }, { signal });
    listbox.addEventListener("pointermove", event => {
        const option = event.target.closest?.("[data-select-option]");
        if (!option || !listbox.contains(option)) return;
        setActive(Number(option.dataset.index), false);
    }, { signal });

    select.addEventListener("change", syncFromNative, { signal });
    select.addEventListener("invalid", event => {
        event.preventDefault();
        root.setAttribute("data-invalid", "");
        button.setAttribute("aria-invalid", "true");
        button.focus();
    }, { signal });
    select.form?.addEventListener("reset", () => {
        cancelAnimationFrame(resetFrame);
        resetFrame = requestAnimationFrame(() => {
            root.removeAttribute("data-invalid");
            button.removeAttribute("aria-invalid");
            syncFromNative();
        });
    }, { signal });

    select.setAttribute("tabindex", "-1");
    select.setAttribute("aria-hidden", "true");
    if (label && button.id) label.setAttribute("for", button.id);
    root.dataset.enhanced = "true";
    ui.hidden = false;
    syncFromNative();

    const destroy = () => {
        close();
        controller.abort();
        cancelAnimationFrame(resetFrame);
        window.clearTimeout(typeaheadTimer);
        delete root.dataset.enhanced;
        root.removeAttribute("data-invalid");
        button.removeAttribute("aria-invalid");
        ui.hidden = true;

        if (previousTabindex === null) select.removeAttribute("tabindex");
        else select.setAttribute("tabindex", previousTabindex);
        if (previousAriaHidden === null) select.removeAttribute("aria-hidden");
        else select.setAttribute("aria-hidden", previousAriaHidden);
        if (label && previousLabelFor !== null) label.setAttribute("for", previousLabelFor);

        delete root.__selectCustomInstance;
    };

    root.__selectCustomInstance = { destroy };
    return destroy;
}
