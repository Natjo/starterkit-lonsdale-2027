const SELECTOR = "[data-lottie-src]";
const IDLE_TIMEOUT = 3000;
const FALLBACK_DELAY = 1500;
const DEFAULT_BREAKPOINT = 768;

const instances = new WeakMap();

const toBoolean = (value, fallback) => {
    if (typeof value === "boolean") return value;
    if (value === "true" || value === "1") return true;
    if (value === "false" || value === "0") return false;
    return fallback;
};

const toSource = (value) => (typeof value === "string" ? value.trim() : "");

/**
 * Un Lottie n'a pas d'équivalent des `<source>` d'un `<picture>` : le JSON est
 * chargé par le script, une seule URL est donc retenue par largeur d'écran.
 */
const mobileQueryFor = (breakpoint) => {
    const width = Number.parseInt(breakpoint, 10);
    const limit = Number.isFinite(width) && width > 0 ? width : DEFAULT_BREAKPOINT;

    return `(max-width: ${limit - 1}px)`;
};

const datasetSource = (element) => {
    const desktop = toSource(element.dataset.lottieSrc);
    const mobile = toSource(element.dataset.lottieSrcMobile);

    if (!desktop || !mobile) return desktop || mobile;

    return window.matchMedia(mobileQueryFor(element.dataset.lottieBreakpoint)).matches
        ? mobile
        : desktop;
};

/**
 * Initialise une animation Lottie.
 *
 * Utilisation autonome : l'hydrateur appelle cette fonction à partir des
 * data-* rendus par component('lottie').
 *
 * Utilisation composée :
 * import initLottie from '@components/lottie/lottie';
 * const lifecycle = initLottie(element, { autoplay: true });
 */
export default function initLottie(root, options = {}) {
    if (!(root instanceof Element)) return;

    const existing = instances.get(root);
    if (existing) return existing;

    const desktopSource = toSource(options.src ?? root.dataset.lottieSrc);
    const mobileSource = toSource(options.mobileSrc ?? root.dataset.lottieSrcMobile);
    const source = desktopSource || mobileSource;

    if (!source) return;

    const mobileQuery = desktopSource && mobileSource
        ? window.matchMedia(mobileQueryFor(options.breakpoint ?? root.dataset.lottieBreakpoint))
        : null;
    const currentSource = () => (mobileQuery?.matches ? mobileSource : source);

    const reducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)");
    const autoplay = toBoolean(options.autoplay ?? root.dataset.lottieAutoplay, false);
    const loop = toBoolean(options.loop ?? root.dataset.lottieLoop, false);
    const progressiveLoad = toBoolean(
        options.progressiveLoad ?? root.dataset.lottieProgressiveLoad,
        true,
    );
    const respectReducedMotion = toBoolean(
        options.respectReducedMotion ?? root.dataset.lottieReducedMotion,
        true,
    );
    const controller = new AbortController();

    let animation = null;
    let destroyed = false;
    let loadedSource = "";
    let paused = !autoplay || (respectReducedMotion && reducedMotion.matches);

    const lifecycle = {
        play: () => {
            if (respectReducedMotion && reducedMotion.matches) return;
            paused = false;
            animation?.play();
        },
        pause: () => {
            paused = true;
            animation?.pause();
        },
        resume: () => {
            if (!autoplay || (respectReducedMotion && reducedMotion.matches)) return;
            paused = false;
            animation?.play();
        },
        stop: () => {
            paused = true;
            animation?.stop();
        },
        destroy: () => {
            destroyed = true;
            controller.abort();
            animation?.destroy();
            animation = null;
            instances.delete(root);
        },
    };

    instances.set(root, lifecycle);

    const load = () => {
        const next = currentSource();

        if (destroyed || !next || next === loadedSource) return;

        delete root.dataset.lottieError;
        loadedSource = next;
        animation?.destroy();
        animation = null;

        Promise.all([
            import("@assets/vendors/lottie/lottie_light.min.js"),
            fetch(next, { signal: controller.signal }).then((response) => {
                if (!response.ok) {
                    throw new Error(`Animation request failed (${response.status})`);
                }

                return response.json();
            }),
        ])
            .then(([{ default: lottie }, animationData]) => {
                // Le player peut arriver après un changement de largeur ou un
                // démontage : ne monter que la source encore attendue.
                if (destroyed || loadedSource !== next) return;

                animation = lottie.loadAnimation({
                    container: root,
                    renderer: "svg",
                    loop,
                    autoplay: !paused,
                    animationData,
                    rendererSettings: { progressiveLoad },
                });

                if (paused) animation.pause();
            })
            .catch((error) => {
                if (error?.name === "AbortError") return;
                if (loadedSource === next) loadedSource = "";
                root.dataset.lottieError = error instanceof Error
                    ? error.message
                    : "load-failed";
                console.error("[Lottie] Failed to load animation", error);
            });
    };

    reducedMotion.addEventListener?.("change", () => {
        if (!respectReducedMotion || !animation) return;

        if (reducedMotion.matches) {
            animation.pause();
        } else if (!paused) {
            animation.play();
        }
    }, { signal: controller.signal });

    mobileQuery?.addEventListener?.("change", load, { signal: controller.signal });

    load();

    return lifecycle;
}

const warmUp = () => {
    // Seule la source retenue pour la largeur courante est mise en cache :
    // préparer les deux annulerait l'économie du choix responsive.
    const sources = [...document.querySelectorAll(SELECTOR)]
        .map(datasetSource)
        .filter(Boolean);

    if (sources.length === 0) return;

    // Échecs sans conséquence : l'initialisation réelle refera le travail et
    // signalera l'erreur si elle persiste.
    import("@assets/vendors/lottie/lottie_light.min.js").catch(() => {});
    [...new Set(sources)].forEach((source) => {
        fetch(source, { cache: "force-cache" }).catch(() => {});
    });
};

/**
 * Sans préparation, une animation différée télécharge, compile et parse son
 * player et son JSON au moment précis où elle approche du viewport. Un temps
 * mort après le chargement suffit à mettre les ressources des composants
 * présents sur la page en cache avant leur affichage.
 */
export function warmLottie() {
    const schedule = () => {
        if ("requestIdleCallback" in window) {
            requestIdleCallback(warmUp, { timeout: IDLE_TIMEOUT });
            return;
        }

        setTimeout(warmUp, FALLBACK_DELAY);
    };

    if (document.readyState === "complete") {
        schedule();
        return;
    }

    window.addEventListener("load", schedule, { once: true });
}
