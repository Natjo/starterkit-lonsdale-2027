const supportsSmoothScroll = () => {
    return !window.matchMedia("(prefers-reduced-motion: reduce)").matches
        && window.matchMedia("(pointer: fine)").matches;
};

const isChrome = () => {
    const brands = navigator.userAgentData?.brands;
    if (Array.isArray(brands)) {
        return brands.some(({ brand }) => brand === "Google Chrome" || brand === "Chromium")
            && !brands.some(({ brand }) => brand === "Microsoft Edge" || brand === "Opera");
    }

    return /\bChrome\//.test(navigator.userAgent)
        && !/\b(?:Edg|OPR)\//.test(navigator.userAgent);
};

export default async function initLenis() {
    if (!supportsSmoothScroll()) return;

    const { default: Lenis } = await import("@assets/vendors/lenis");

    window.lenis = new Lenis({
        autoRaf: true,
        lerp: 0.14,
        wheelMultiplier: isChrome() ? 0.5 : 0.8,
    });
    window.dispatchEvent(new CustomEvent("lenis:ready", { detail: window.lenis }));

    return window.lenis;
}
