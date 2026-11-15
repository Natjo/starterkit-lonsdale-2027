import initDesignSystem from './design-system.js';
const root = document.querySelector('[data-forge-design-system]');
if (root) {
    const dispose = initDesignSystem(root);
    window.addEventListener('pagehide', () => dispose?.(), { once: true });
}

const commonChoices = document.querySelector('[data-common-choices]');
if (commonChoices) {
    const { default: initCommonPreview } = await import('./common-preview.js');
    const dispose = initCommonPreview(commonChoices);
    window.addEventListener('pagehide', dispose, { once: true });
}
