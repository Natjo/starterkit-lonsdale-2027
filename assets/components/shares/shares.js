const COPY_FEEDBACK_DURATION = 1800;

const getHttpUrl = value => {
    try {
        const url = new URL(value, window.location.href);
        return ["http:", "https:"].includes(url.protocol) ? url.href : "";
    } catch (_) {
        return "";
    }
};

const legacyCopy = text => {
    const activeElement = document.activeElement;
    const selection = document.getSelection();
    const ranges = selection
        ? Array.from({ length: selection.rangeCount }, (_, index) => selection.getRangeAt(index).cloneRange())
        : [];
    const textarea = document.createElement("textarea");

    textarea.value = text;
    textarea.readOnly = true;
    textarea.style.position = "fixed";
    textarea.style.inset = "-9999px auto auto -9999px";
    document.body.appendChild(textarea);
    textarea.focus({ preventScroll: true });
    textarea.select();

    let copied = false;
    try {
        copied = document.execCommand("copy");
    } catch (_) {
        copied = false;
    }

    textarea.remove();
    if (activeElement instanceof HTMLElement && activeElement.isConnected) {
        activeElement.focus({ preventScroll: true });
    }
    if (selection && ranges.length > 0) {
        selection.removeAllRanges();
        ranges.forEach(range => selection.addRange(range));
    }

    return copied;
};

const copyToClipboard = async text => {
    if (!text) return false;

    if (navigator.clipboard && window.isSecureContext) {
        try {
            await navigator.clipboard.writeText(text);
            return true;
        } catch (_) {
            // Le fallback reste utile lorsque la permission Clipboard est refusée.
        }
    }

    return legacyCopy(text);
};

export default function shares(root) {
    if (!(root instanceof HTMLElement) || root.__sharesInstance) return;

    let destroyed = false;
    const timers = new Set();
    const clearTimers = () => {
        timers.forEach(timer => window.clearTimeout(timer));
        timers.clear();
    };
    const later = (callback, delay) => {
        const timer = window.setTimeout(() => {
            timers.delete(timer);
            callback();
        }, delay);
        timers.add(timer);
    };
    const showCopyFeedback = (button, copied) => {
        const message = copied ? button.dataset.success || "" : button.dataset.error || "";
        const status = button.querySelector("[data-copy-status]");
        const tip = button.querySelector("[data-copy-tip]");

        clearTimers();
        button.classList.toggle("is-copied", copied);
        button.classList.toggle("is-copy-error", !copied);
        if (tip) tip.textContent = message;

        if (status) {
            status.textContent = "";
            later(() => {
                if (!destroyed && button.isConnected) status.textContent = message;
            }, 0);
        }

        later(() => {
            if (destroyed || !button.isConnected) return;
            button.classList.remove("is-copied", "is-copy-error");
            if (tip) tip.textContent = "";
            if (status) status.textContent = "";
        }, COPY_FEEDBACK_DURATION);
    };
    const onClick = async event => {
        const target = event.target instanceof Element ? event.target : null;
        if (!target) return;

        const copyButton = target.closest("button[data-share-copy][data-url]");
        if (copyButton && root.contains(copyButton)) {
            const url = getHttpUrl(copyButton.dataset.url || "");
            const copied = url !== "" && await copyToClipboard(url);
            if (!destroyed && copyButton.isConnected) showCopyFeedback(copyButton, copied);
            return;
        }

    };
    const destroy = () => {
        if (destroyed) return;
        destroyed = true;
        root.removeEventListener("click", onClick);
        clearTimers();
        root.querySelectorAll("[data-share-copy]").forEach(button => {
            button.classList.remove("is-copied", "is-copy-error");
        });
        delete root.__sharesInstance;
    };

    root.addEventListener("click", onClick);
    root.__sharesInstance = { destroy };

    return destroy;
}
