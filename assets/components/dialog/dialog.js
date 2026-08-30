const CLOSE_FALLBACK_MS = 2000;
const lastTriggerByDialog = new WeakMap();
const pendingCloseByDialog = new WeakMap();

const isOpen = dialog => dialog.open || dialog.hasAttribute("open");

const closeDialog = (dialog, returnValue = "close") => {
    if (!isOpen(dialog)) return;

    if (typeof dialog.close === "function") {
        dialog.close(returnValue);
    } else {
        dialog.removeAttribute("open");
        dialog.dispatchEvent(new Event("close"));
    }
};

const releaseTrigger = (dialog, restoreFocus = true) => {
    const trigger = lastTriggerByDialog.get(dialog);
    lastTriggerByDialog.delete(dialog);
    if (!trigger) return;

    trigger.setAttribute("aria-expanded", "false");
    if (restoreFocus && trigger.isConnected && typeof trigger.focus === "function") {
        window.requestAnimationFrame(() => {
            if (!isOpen(dialog) && trigger.isConnected) trigger.focus();
        });
    }
};

const cancelPendingClose = dialog => {
    pendingCloseByDialog.get(dialog)?.();
};

const requestClose = (dialog, returnValue = "close") => {
    if (!isOpen(dialog) || pendingCloseByDialog.has(dialog)) return;

    if (window.matchMedia("(prefers-reduced-motion: reduce)").matches) {
        releaseTrigger(dialog);
        closeDialog(dialog, returnValue);
        return;
    }

    dialog.setAttribute("data-dialog-closing", "1");
    if (!getComputedStyle(dialog).animationName.split(",").map(name => name.trim()).includes("dialog-fade-out")) {
        releaseTrigger(dialog);
        closeDialog(dialog, returnValue);
        return;
    }

    let fallbackTimer = 0;
    const cleanup = () => {
        dialog.removeEventListener("animationend", onAnimationEnd);
        if (fallbackTimer) window.clearTimeout(fallbackTimer);
        pendingCloseByDialog.delete(dialog);
    };
    const finish = () => {
        cleanup();
        releaseTrigger(dialog);
        closeDialog(dialog, returnValue);
    };
    const onAnimationEnd = event => {
        if (event.target === dialog && event.animationName === "dialog-fade-out") finish();
    };

    dialog.addEventListener("animationend", onAnimationEnd);
    fallbackTimer = window.setTimeout(finish, CLOSE_FALLBACK_MS);
    pendingCloseByDialog.set(dialog, cleanup);
};

const isBackdropClick = (dialog, event) => {
    if (event.target !== dialog) return false;

    const rect = dialog.getBoundingClientRect();
    return event.clientX < rect.left
        || event.clientX > rect.right
        || event.clientY < rect.top
        || event.clientY > rect.bottom;
};

class Dialog {
    constructor(dialog) {
        this.dialog = dialog;
        this.cleanups = [];

        this.bindDialogEvents();
        this.bindTriggers();
    }

    listen(target, eventName, listener) {
        target.addEventListener(eventName, listener);
        this.cleanups.push(() => target.removeEventListener(eventName, listener));
    }

    bindDialogEvents() {
        const { dialog } = this;

        this.listen(dialog, "click", event => {
            const target = event.target;
            const closeButton = target instanceof Element
                ? target.closest("[data-dialog-close]")
                : null;

            if (closeButton && dialog.contains(closeButton)) {
                event.preventDefault();
                const returnValue = closeButton instanceof HTMLButtonElement && closeButton.value
                    ? closeButton.value
                    : "close";
                requestClose(dialog, returnValue);
                return;
            }

            if (isBackdropClick(dialog, event)) {
                requestClose(dialog, "backdrop");
            }
        });

        this.listen(dialog, "cancel", event => {
            event.preventDefault();
            requestClose(dialog, "cancel");
        });

        this.listen(dialog, "close", () => {
            cancelPendingClose(dialog);
            dialog.removeAttribute("data-dialog-closing");
            releaseTrigger(dialog);
        });
    }

    bindTriggers() {
        const { dialog } = this;
        if (!dialog.id) return;

        const selector = `[data-dialog-id="${CSS.escape(dialog.id)}"]`;
        document.querySelectorAll(selector).forEach(trigger => {
            this.listen(trigger, "click", event => {
                if (trigger instanceof HTMLAnchorElement) event.preventDefault();
                if (trigger instanceof HTMLButtonElement && trigger.disabled) return;
                if (trigger.getAttribute("aria-disabled") === "true" || isOpen(dialog)) return;

                lastTriggerByDialog.set(dialog, trigger);
                trigger.setAttribute("aria-expanded", "true");

                try {
                    dialog.showModal();
                } catch (_) {
                    trigger.setAttribute("aria-expanded", "false");
                    lastTriggerByDialog.delete(dialog);
                }
            });
        });
    }

    destroy() {
        cancelPendingClose(this.dialog);
        this.dialog.removeAttribute("data-dialog-closing");
        releaseTrigger(this.dialog);
        closeDialog(this.dialog, "destroy");

        this.cleanups.forEach(cleanup => cleanup());
        this.cleanups = [];
        delete this.dialog.__dialogInstance;
    }
}

export default element => {
    if (typeof HTMLDialogElement === "undefined" || !(element instanceof HTMLDialogElement)) return;
    if (element.__dialogInstance) return;

    element.__dialogInstance = new Dialog(element);
    return () => element.__dialogInstance?.destroy();
};
