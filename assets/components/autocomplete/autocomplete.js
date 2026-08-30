const normalize = (value) => String(value || "")
    .normalize("NFD")
    .replace(/[\u0300-\u036f]/g, "")
    .trim()
    .toLowerCase();

class Autocomplete {
    constructor(root) {
        if (!(root instanceof HTMLElement)) return;
        if (root.__autocompleteInstance) return root.__autocompleteInstance;

        this.root = root;
        this.label = root.querySelector("label");
        this.select = root.querySelector("select");
        this.textBox = root.querySelector('[role="combobox"]');
        this.menu = root.querySelector('[role="listbox"]');
        this.status = root.querySelector('[role="status"]');
        if (!this.select || !this.textBox || !this.menu) return;

        this.required = this.select.required;
        this.options = this._readOptions();
        this.visibleOptions = [];
        this.activeIndex = -1;
        this.open = false;
        this.destroyed = false;
        this.syncingSelect = false;
        this.controller = new AbortController();
        this.outsideController = null;

        this._onInput = this._onInput.bind(this);
        this._onTextBoxKeyDown = this._onTextBoxKeyDown.bind(this);
        this._onMenuClick = this._onMenuClick.bind(this);
        this._onMenuPointerDown = this._onMenuPointerDown.bind(this);
        this._onMenuPointerMove = this._onMenuPointerMove.bind(this);
        this._onDocumentPointerDown = this._onDocumentPointerDown.bind(this);
        this._onSelectChange = this._onSelectChange.bind(this);
        this._onInvalid = this._onInvalid.bind(this);
        this._onFormReset = this._onFormReset.bind(this);

        const { signal } = this.controller;
        this.textBox.addEventListener("input", this._onInput, { signal });
        this.textBox.addEventListener("keydown", this._onTextBoxKeyDown, { signal });
        this.textBox.addEventListener("invalid", this._onInvalid, { signal });
        this.menu.addEventListener("click", this._onMenuClick, { signal });
        this.menu.addEventListener("pointerdown", this._onMenuPointerDown, { signal });
        this.menu.addEventListener("pointermove", this._onMenuPointerMove, { signal });
        this.select.addEventListener("change", this._onSelectChange, { signal });
        this.select.form?.addEventListener("reset", this._onFormReset, { signal });

        this._hideMenu();
        this._enhance();
        this._syncInputFromSelect();
        this._validate(false);

        root.__autocompleteInstance = this;
    }

    _enhance() {
        this.select.required = false;
        this.select.tabIndex = -1;
        this.select.setAttribute("aria-hidden", "true");
        this.textBox.required = this.required;
        if (this.required) this.textBox.setAttribute("aria-required", "true");
        this.label?.setAttribute("for", this.textBox.id);
        this.root.classList.add("is-ready");
    }

    _readOptions() {
        return Array.from(this.select.options)
            .map((option, index) => ({
                index,
                text: option.text.trim(),
                value: option.value,
                alt: option.dataset.alt || "",
                disabled: option.disabled,
            }))
            .filter(({ text, value, disabled }) => !disabled && value.trim() !== "" && text !== "");
    }

    _filterOptions(value) {
        const needle = normalize(value);
        if (needle === "") return this.options;

        return this.options.filter(({ text, alt }) =>
            normalize(text).includes(needle) || normalize(alt).includes(needle));
    }

    _findExact(value) {
        const needle = normalize(value);
        if (needle === "") return null;
        return this.options.find(({ text }) => normalize(text) === needle) || null;
    }

    _onInput(event) {
        if (event.isComposing) return;

        const value = this.textBox.value;
        const trimmedValue = value.trim();
        const options = this._filterOptions(value);
        const exact = this._findExact(value);

        this._setSelectValue(exact?.value || "");
        this._validate(true);

        if (trimmedValue === "") {
            this._hideMenu();
            this._updateStatus(null);
            return;
        }

        this._buildMenu(options);
        if (options.length > 0) this._showMenu();
        else this._hideMenu();
        this._updateStatus(options.length);
    }

    _onTextBoxKeyDown(event) {
        if (event.isComposing) return;

        if (event.key === "ArrowDown" || event.key === "ArrowUp") {
            event.preventDefault();
            if (!this.open) this._openFromCurrentValue();
            if (!this.open) return;

            const delta = event.key === "ArrowDown" ? 1 : -1;
            const fallback = event.key === "ArrowDown" ? 0 : this.visibleOptions.length - 1;
            const next = this.activeIndex === -1
                ? fallback
                : Math.max(-1, Math.min(this.visibleOptions.length - 1, this.activeIndex + delta));
            this._setActive(next);
            return;
        }

        if (event.key === "Enter" && this.open && this.activeIndex >= 0) {
            event.preventDefault();
            this._selectOption(this.visibleOptions[this.activeIndex]);
            return;
        }

        if (event.key === "Escape") {
            if (this.open) event.preventDefault();
            this._hideMenu();
            return;
        }

        if (event.key === "Tab") {
            this._hideMenu();
            this._validate(true);
        }
    }

    _openFromCurrentValue() {
        const options = this._filterOptions(this.textBox.value);
        this._buildMenu(options);
        if (options.length > 0) this._showMenu();
        else this._hideMenu();
        this._updateStatus(options.length);
    }

    _onMenuPointerDown(event) {
        if (event.target instanceof Element && event.target.closest('[role="option"]')) {
            event.preventDefault();
        }
    }

    _onMenuPointerMove(event) {
        if (!(event.target instanceof Element)) return;
        const option = event.target.closest('[role="option"]');
        if (!option || !this.menu.contains(option)) return;
        const index = Number(option.dataset.visibleIndex);
        if (Number.isInteger(index) && index !== this.activeIndex) this._setActive(index);
    }

    _onMenuClick(event) {
        if (!(event.target instanceof Element)) return;
        const option = event.target.closest('[role="option"]');
        if (!option || !this.menu.contains(option)) return;
        const index = Number(option.dataset.visibleIndex);
        if (Number.isInteger(index) && this.visibleOptions[index]) {
            this._selectOption(this.visibleOptions[index]);
        }
    }

    _onDocumentPointerDown(event) {
        if (!(event.target instanceof Node) || !this.root.contains(event.target)) this._hideMenu();
    }

    _onSelectChange() {
        if (!this.syncingSelect) this._syncInputFromSelect();
    }

    _onInvalid() {
        this.textBox.setAttribute("aria-invalid", "true");
        this._updateStatus(this.textBox.validationMessage || "Veuillez sélectionner une valeur proposée.");
    }

    _onFormReset() {
        window.setTimeout(() => {
            if (this.destroyed) return;
            this._syncInputFromSelect();
            this._hideMenu();
            this._validate(false);
        }, 0);
    }

    _buildMenu(options) {
        const fragment = document.createDocumentFragment();
        const baseId = this.menu.id || "autocomplete-menu";
        this.visibleOptions = options;

        options.forEach((option, index) => {
            const item = document.createElement("li");
            item.id = `${baseId}__${option.index}`;
            item.setAttribute("role", "option");
            item.setAttribute("aria-selected", "false");
            item.dataset.visibleIndex = String(index);
            item.textContent = option.text;
            fragment.appendChild(item);
        });

        this.menu.replaceChildren(fragment);
        this._setActive(-1);
    }

    _setActive(index) {
        const previous = this.menu.querySelector('[aria-selected="true"]');
        previous?.setAttribute("aria-selected", "false");

        this.activeIndex = index;
        const options = this.menu.querySelectorAll('[role="option"]');
        const active = index >= 0 ? options[index] : null;

        if (!active) {
            this.textBox.removeAttribute("aria-activedescendant");
            return;
        }

        active.setAttribute("aria-selected", "true");
        this.textBox.setAttribute("aria-activedescendant", active.id);
        this._scrollIntoView(active);
    }

    _selectOption(option) {
        if (!option) return;
        this.textBox.value = option.text;
        this._setSelectValue(option.value);
        this._validate(true);
        this._hideMenu();
        this.textBox.focus();
    }

    _setSelectValue(value) {
        if (this.select.value === value) return;

        this.select.value = value;
        this._validate(false);
        this.syncingSelect = true;
        try {
            this.select.dispatchEvent(new Event("input", { bubbles: true }));
            this.select.dispatchEvent(new Event("change", { bubbles: true }));
        } finally {
            this.syncingSelect = false;
        }
    }

    _syncInputFromSelect() {
        const option = this.select.selectedOptions[0];
        this.textBox.value = option?.value ? option.text.trim() : "";
        this._validate(false);
    }

    _validate(showState) {
        const hasText = this.textBox.value.trim() !== "";
        const hasSelection = this.select.value !== "";
        const valid = hasText ? hasSelection : !this.required;
        const message = valid
            ? ""
            : (hasText ? "Veuillez sélectionner une valeur proposée." : "Veuillez renseigner ce champ.");

        this.textBox.setCustomValidity(message);
        if (showState && !valid) this.textBox.setAttribute("aria-invalid", "true");
        else this.textBox.removeAttribute("aria-invalid");
        return valid;
    }

    _showMenu() {
        if (this.visibleOptions.length === 0) return;

        this.menu.classList.remove("hidden");
        this.textBox.setAttribute("aria-expanded", "true");
        this.open = true;

        if (!this.outsideController) {
            this.outsideController = new AbortController();
            document.addEventListener("pointerdown", this._onDocumentPointerDown, {
                signal: this.outsideController.signal,
            });
        }
    }

    _hideMenu() {
        this.menu.classList.add("hidden");
        this.textBox.setAttribute("aria-expanded", "false");
        this._setActive(-1);
        this.open = false;
        this.outsideController?.abort();
        this.outsideController = null;
    }

    _updateStatus(value) {
        if (!this.status) return;
        if (value === null) this.status.textContent = "";
        else if (typeof value === "string") this.status.textContent = value;
        else if (value > 0) {
            this.status.textContent = `${value} résultat${value > 1 ? "s" : ""} disponible${value > 1 ? "s" : ""}.`;
        } else this.status.textContent = "Aucun résultat disponible.";
    }

    _scrollIntoView(option) {
        const top = option.offsetTop;
        const bottom = top + option.offsetHeight;
        if (top < this.menu.scrollTop) this.menu.scrollTop = top;
        else if (bottom > this.menu.scrollTop + this.menu.clientHeight) {
            this.menu.scrollTop = bottom - this.menu.clientHeight;
        }
    }

    destroy() {
        if (this.destroyed) return;
        this.destroyed = true;
        const restoreFocus = document.activeElement === this.textBox && this.root.isConnected;

        this._hideMenu();
        this.controller.abort();
        this.menu.replaceChildren();
        if (this.status) this.status.textContent = "";
        this.textBox.setCustomValidity("");
        this.textBox.required = false;
        this.textBox.removeAttribute("aria-required");
        this.textBox.removeAttribute("aria-invalid");
        this.textBox.removeAttribute("aria-activedescendant");
        this.select.required = this.required;
        this.select.removeAttribute("aria-hidden");
        this.select.removeAttribute("tabindex");
        this.label?.setAttribute("for", this.select.id);
        this.root.classList.remove("is-ready");
        if (restoreFocus) this.select.focus();
        delete this.root.__autocompleteInstance;
    }
}

export { Autocomplete };

export default function initAutocomplete(root) {
    const instance = new Autocomplete(root);
    if (!instance?.root) return;

    return () => instance.destroy();
}
