const OPEN_ATTRIBUTE = "data-tooltip-open";
const DISMISSED_ATTRIBUTE = "data-tooltip-dismissed";

export default tooltip => {
    if (!(tooltip instanceof HTMLElement)) return;

    const trigger = tooltip.querySelector(":scope > .trigger-button");
    const content = tooltip.querySelector(":scope > .tooltip-content");
    if (!(trigger instanceof HTMLButtonElement) || !(content instanceof HTMLElement)) return;

    const controller = new AbortController();
    const { signal } = controller;
    let lastPointerType = "";
    let openAtPointerDown = false;

    const updatePosition = () => {
        content.style.removeProperty("--tooltip-shift-x");

        const margin = 16;
        const bounds = content.getBoundingClientRect();
        let shift = 0;

        if (bounds.left < margin) shift = margin - bounds.left;
        if (bounds.right > window.innerWidth - margin) {
            shift += window.innerWidth - margin - bounds.right;
        }

        content.style.setProperty("--tooltip-shift-x", `${Math.round(shift)}px`);
    };

    const open = () => {
        tooltip.removeAttribute(DISMISSED_ATTRIBUTE);
        tooltip.setAttribute(OPEN_ATTRIBUTE, "true");
        requestAnimationFrame(updatePosition);
    };

    const close = ({ dismiss = false } = {}) => {
        tooltip.removeAttribute(OPEN_ATTRIBUTE);
        if (dismiss) tooltip.setAttribute(DISMISSED_ATTRIBUTE, "true");
    };

    tooltip.addEventListener("pointerenter", event => {
        if (event.pointerType === "mouse") open();
    }, { signal });
    tooltip.addEventListener("pointerleave", () => {
        close();
        if (!(document.activeElement instanceof Node) || !tooltip.contains(document.activeElement)) {
            tooltip.removeAttribute(DISMISSED_ATTRIBUTE);
        }
    }, { signal });

    tooltip.addEventListener("focusin", open, { signal });
    tooltip.addEventListener("focusout", event => {
        if (!(event.relatedTarget instanceof Node) || !tooltip.contains(event.relatedTarget)) {
            close();
        }
    }, { signal });

    trigger.addEventListener("pointerdown", event => {
        lastPointerType = event.pointerType;
        openAtPointerDown = tooltip.hasAttribute(OPEN_ATTRIBUTE);
    }, { signal });

    trigger.addEventListener("click", event => {
        if (event.detail === 0 || lastPointerType === "mouse") return;

        if (openAtPointerDown) {
            close({ dismiss: true });
        } else {
            open();
        }
    }, { signal });

    tooltip.addEventListener("keydown", event => {
        if (event.key !== "Escape") return;
        event.preventDefault();
        close({ dismiss: true });
    }, { signal });

    document.addEventListener("pointerdown", event => {
        if (event.target instanceof Node && !tooltip.contains(event.target)) {
            close({ dismiss: true });
        }
    }, { signal });

    window.addEventListener("resize", updatePosition, { signal, passive: true });

    return () => {
        controller.abort();
        content.style.removeProperty("--tooltip-shift-x");
        tooltip.removeAttribute(OPEN_ATTRIBUTE);
        tooltip.removeAttribute(DISMISSED_ATTRIBUTE);
    };
};
