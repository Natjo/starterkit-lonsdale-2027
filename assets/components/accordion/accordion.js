
export default (el) => {
    const multiple = el.dataset.multiple === "true";
    const items = [...el.querySelectorAll(":scope > .details")]
        .map(detail => {
            const button = detail.querySelector(":scope > h3 > .summary");
            const panel = detail.querySelector(":scope > .details-content");

            return button && panel && button.getAttribute("aria-controls") === panel.id
                ? { button, panel, expanded: button.getAttribute("aria-expanded") === "true" }
                : null;
        })
        .filter(Boolean);

    if (!items.length) return;

    const setExpanded = (item, expanded) => {
        if (item.expanded === expanded) return;

        item.expanded = expanded;
        item.button.setAttribute("aria-expanded", expanded);
        item.panel.setAttribute("aria-hidden", !expanded);
    };

    const toggle = selected => {
        const shouldOpen = !selected.expanded;

        if (multiple) {
            setExpanded(selected, shouldOpen);
            return;
        }

        items.forEach(item => {
            setExpanded(item, item === selected ? shouldOpen : false);
        });
    };

    const controller = new AbortController();
    items.forEach(item => {
        item.button.addEventListener("click", () => toggle(item), { signal: controller.signal });
    });

    return () => controller.abort();
};
