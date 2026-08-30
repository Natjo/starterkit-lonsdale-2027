/**
 * Valeurs à maintenir alignées avec `assets/styles/_breakpoints.scss`.
 */
export const BREAKPOINTS = Object.freeze({
    xs: 480,
    sm: 576,
    md: 768,
    lg: 1025,
    xlg: 1200,
    wd: 1440,
    xwd: 1920,
    xlgwd: 2400,
});

const noop = () => {};

/**
 * Observe le franchissement d'un breakpoint avec `matchMedia`.
 *
 * Le callback correspondant à l'état courant est exécuté immédiatement, puis
 * uniquement lorsque la media query change d'état.
 *
 * @param {number} value Breakpoint en pixels.
 * @param {{above?: Function, under?: Function}} handlers
 * @returns {Function} Fonction de nettoyage.
 */
export default function breakpoint(value, handlers = {}) {
    if (!Number.isFinite(value) || value < 0) return noop;
    if (typeof window === "undefined" || typeof window.matchMedia !== "function") return noop;

    const above = typeof handlers?.above === "function" ? handlers.above : noop;
    const under = typeof handlers?.under === "function" ? handlers.under : noop;
    const media = window.matchMedia(`(min-width: ${value}px)`);
    let active = true;

    const update = event => {
        if (!active) return;
        (event.matches ? above : under)(event);
    };

    if (typeof media.addEventListener === "function") {
        media.addEventListener("change", update);
    } else {
        media.addListener?.(update);
    }

    update(media);

    return () => {
        if (!active) return;
        active = false;

        if (typeof media.removeEventListener === "function") {
            media.removeEventListener("change", update);
        } else {
            media.removeListener?.(update);
        }
    };
}
