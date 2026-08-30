const activeSplits = new WeakMap();
const emptyResult = () => ({ words: [], chars: [], restore: () => {} });

let graphemeSegmenter;

const splitGraphemes = value => {
    if (typeof globalThis.Intl?.Segmenter !== "function") return Array.from(value);

    graphemeSegmenter ??= new globalThis.Intl.Segmenter(undefined, { granularity: "grapheme" });
    return Array.from(graphemeSegmenter.segment(value), item => item.segment);
};

const normalizeClass = (value, fallback) => {
    if (typeof value !== "string") return fallback;

    const normalized = value.trim();
    return normalized || fallback;
};

const normalizeSelector = (element, value) => {
    if (typeof value !== "string" || value.trim() === "") return "";

    const selector = value.trim();

    try {
        element.matches(selector);
        return selector;
    } catch {
        return "";
    }
};

const getAccessibleText = element => {
    const clone = element.cloneNode(true);
    const doc = element.ownerDocument;

    clone.querySelectorAll("br").forEach(br => {
        br.replaceWith(doc.createTextNode(" "));
    });

    return (clone.textContent ?? "").replace(/\s+/g, " ").trim();
};

/**
 * Découpe les nœuds texte d'un élément en spans de mots et de graphèmes.
 *
 * Le HTML inline reste en place. `restore()` retire uniquement les nœuds
 * générés et rétablit les nœuds texte et attributs d'origine.
 *
 * @param {Element} targetEl
 * @param {{wordClass?: string, charClass?: string, skipSelector?: string}} options
 * @returns {{words: HTMLSpanElement[], chars: HTMLSpanElement[], restore: Function}}
 */
export function splitText(targetEl, options = {}) {
    if (!targetEl || targetEl.nodeType !== 1 || !targetEl.ownerDocument) return emptyResult();

    activeSplits.get(targetEl)?.restore();

    const wordClass = normalizeClass(options?.wordClass, "word");
    const charClass = normalizeClass(options?.charClass, "char");
    const skipSelector = normalizeSelector(targetEl, options?.skipSelector);
    const doc = targetEl.ownerDocument;
    const view = doc.defaultView;
    const nodeFilter = view?.NodeFilter ?? globalThis.NodeFilter;

    if (!nodeFilter || typeof doc.createTreeWalker !== "function") return emptyResult();

    const hadAriaLabel = targetEl.hasAttribute("aria-label");
    const originalAriaLabel = targetEl.getAttribute("aria-label");
    const accessibleText = getAccessibleText(targetEl);

    if (!hadAriaLabel && accessibleText !== "") {
        targetEl.setAttribute("aria-label", accessibleText);
    }

    const words = [];
    const chars = [];
    const records = [];
    const textNodes = [];
    const walker = doc.createTreeWalker(targetEl, nodeFilter.SHOW_TEXT, {
        acceptNode: node => {
            if (!skipSelector) return nodeFilter.FILTER_ACCEPT;

            return node.parentElement?.closest?.(skipSelector)
                ? nodeFilter.FILTER_REJECT
                : nodeFilter.FILTER_ACCEPT;
        },
    });

    while (walker.nextNode()) {
        textNodes.push(walker.currentNode);
    }

    for (const textNode of textNodes) {
        const text = textNode.nodeValue ?? "";
        if (text === "") continue;

        const fragment = doc.createDocumentFragment();
        const generatedNodes = [];
        const parts = text.split(/(\s+)/);

        for (const part of parts) {
            if (part === "") continue;

            if (/^\s+$/.test(part)) {
                const whitespace = doc.createTextNode(part);
                fragment.appendChild(whitespace);
                generatedNodes.push(whitespace);
                continue;
            }

            const wordSpan = doc.createElement("span");
            wordSpan.className = wordClass;

            for (const char of splitGraphemes(part)) {
                const charSpan = doc.createElement("span");
                charSpan.className = charClass;
                charSpan.textContent = char;
                wordSpan.appendChild(charSpan);
                chars.push(charSpan);
            }

            words.push(wordSpan);
            fragment.appendChild(wordSpan);
            generatedNodes.push(wordSpan);
        }

        textNode.nodeValue = "";
        textNode.after(fragment);
        records.push({ textNode, text, generatedNodes });
    }

    let restored = false;
    const result = { words, chars, restore };

    function restore() {
        if (restored) return;
        restored = true;

        for (const { textNode, text, generatedNodes } of records) {
            generatedNodes.forEach(node => node.remove());
            textNode.nodeValue = text;
        }

        if (hadAriaLabel) {
            targetEl.setAttribute("aria-label", originalAriaLabel ?? "");
        } else {
            targetEl.removeAttribute("aria-label");
        }

        if (activeSplits.get(targetEl) === result) {
            activeSplits.delete(targetEl);
        }
    }

    activeSplits.set(targetEl, result);
    return result;
}
