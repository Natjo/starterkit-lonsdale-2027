const DEFAULT_DURATION = 1600;
const GROUP_SEPARATOR_RE = /[\s\u00a0\u202f']/;

const normalizeElements = (elements) => {
    if (!elements) return [];

    if (typeof elements[Symbol.iterator] === "function") {
        return [...elements];
    }

    return [elements];
};

const parseValue = (element) => {
    if (!element || typeof element.textContent !== "string") return null;

    const text = element.textContent.trim();
    if (!/^[+-]?[\d\s\u00a0\u202f'.,]+$/.test(text)) return null;

    const unsigned = text.replace(/^[+-]/, "");
    const commaIndex = unsigned.lastIndexOf(",");
    const dotIndex = unsigned.lastIndexOf(".");
    const decimalSeparator = commaIndex >= 0 || dotIndex >= 0
        ? (commaIndex > dotIndex ? "," : ".")
        : "";
    const decimalIndex = decimalSeparator !== ""
        ? unsigned.lastIndexOf(decimalSeparator)
        : -1;
    const decimals = decimalIndex >= 0 ? unsigned.length - decimalIndex - 1 : 0;
    const integerSource = decimalIndex >= 0 ? unsigned.slice(0, decimalIndex) : unsigned;
    const groupSeparator = integerSource.match(GROUP_SEPARATOR_RE)?.[0] ?? "";
    const compact = text.replace(/[\s\u00a0\u202f']/g, "");
    const normalized = decimalSeparator !== ""
        ? compact.replace(decimalSeparator, ".")
        : compact;

    if (!/^[+-]?\d+(?:\.\d+)?$/.test(normalized)) return null;

    const target = Number(normalized);
    if (!Number.isFinite(target)) return null;

    return {
        element,
        original: text,
        target,
        decimals,
        decimalSeparator,
        groupSeparator,
    };
};

const formatValue = (entry, value) => {
    const threshold = entry.decimals > 0 ? 0.5 / (10 ** entry.decimals) : 0.5;
    const safeValue = Math.abs(value) < threshold ? 0 : value;
    const [rawInteger, fraction = ""] = Math.abs(safeValue)
        .toFixed(entry.decimals)
        .split(".");
    const integer = entry.groupSeparator !== ""
        ? rawInteger.replace(/\B(?=(\d{3})+(?!\d))/g, entry.groupSeparator)
        : rawInteger;
    const sign = safeValue < 0 ? "-" : "";

    return sign + integer + (
        entry.decimals > 0
            ? entry.decimalSeparator + fraction
            : ""
    );
};

const easeOutCubic = (progress) => 1 - ((1 - progress) ** 3);

export default function numberCounter(elements, options = {}) {
    options = options && typeof options === "object" ? options : {};

    const entries = normalizeElements(elements)
        .map(parseValue)
        .filter(Boolean);
    const requestedDuration = Number(options.duration);
    const duration = Number.isFinite(requestedDuration) && requestedDuration > 0
        ? requestedDuration
        : DEFAULT_DURATION;
    const reducedMotion = window.matchMedia?.("(prefers-reduced-motion: reduce)").matches === true;

    if (entries.length === 0 || reducedMotion) return;

    let frame = null;
    let startedAt = null;
    let destroyed = false;

    const render = (progress) => {
        const easedProgress = easeOutCubic(progress);

        for (const entry of entries) {
            entry.element.textContent = formatValue(entry, entry.target * easedProgress);
        }
    };

    const tick = (timestamp) => {
        if (destroyed) return;
        if (startedAt === null) startedAt = timestamp;

        const progress = Math.min(1, (timestamp - startedAt) / duration);
        render(progress);

        if (progress < 1) {
            frame = requestAnimationFrame(tick);
            return;
        }

        for (const entry of entries) {
            entry.element.textContent = entry.original;
        }

        frame = null;
    };

    render(0);
    frame = requestAnimationFrame(tick);

    return () => {
        if (destroyed) return;
        destroyed = true;

        if (frame !== null) {
            cancelAnimationFrame(frame);
            frame = null;
        }

        for (const entry of entries) {
            entry.element.textContent = entry.original;
        }
    };
}
