/**
 * Hydratation progressive des modules data-module.
 *
 * - Chargement immédiat par défaut
 * - data-context="@visible" : lazy via IntersectionObserver partagé
 * - lifecycle unique ou tableau de lifecycles partiels
 * - modules avec { pause, resume, destroy } : pause hors viewport (IO lifecycle)
 * - data-module-media : chargement conditionnel, repris au changement de media query
 */

const hydratedElements = new WeakMap();
const moduleCache = new Map();
const pendingLoads = new Map();
const visibilityWaiters = new Map();
const mediaWatchers = new WeakMap();
const hydrationTokens = new WeakMap();
const lifecycleEntries = new Map();

const defaultObserverOptions = {
    root: null,
    rootMargin: "50px",
    threshold: 0.1,
};

let visibilityObserver = null;
let lifecycleObserver = null;

const getModuleElements = (container) => {
    const elements = [];

    if (container?.matches?.("[data-module]")) {
        elements.push(container);
    }

    container?.querySelectorAll?.("[data-module]").forEach((element) => elements.push(element));
    return elements;
};

const isValidModuleName = (moduleName) =>
    /^(?:common|components|heros|strates)\/[A-Za-z0-9_-]+$/.test(moduleName);

const isCurrentHydration = (element, token, moduleName) =>
    element.isConnected
    && hydrationTokens.get(element) === token
    && element.dataset.module?.trim() === moduleName;

const getVisibilityObserver = () => {
    if (visibilityObserver) {
        return visibilityObserver;
    }

    if (!("IntersectionObserver" in window)) {
        return null;
    }

    visibilityObserver = new IntersectionObserver((entries) => {
        for (const entry of entries) {
            if (!entry.isIntersecting) {
                continue;
            }

            const waiter = visibilityWaiters.get(entry.target);

            if (!waiter) {
                continue;
            }

            waiter.finish(true);
        }
    }, defaultObserverOptions);

    return visibilityObserver;
};

const shouldWaitForVisibility = (element) =>
    element.dataset.context?.includes("@visible") ?? false;

const stopMediaWatcher = (element) => {
    const watcher = mediaWatchers.get(element);
    if (!watcher) return;

    watcher.mql.removeEventListener?.("change", watcher.onChange);
    watcher.mql.removeListener?.(watcher.onChange);
    mediaWatchers.delete(element);
};

const watchMediaCondition = (element) => {
    const media = element.dataset.moduleMedia;

    if (!media) {
        return true;
    }

    let mql;

    try {
        mql = window.matchMedia(media);
    } catch (error) {
        console.error(`[Hydrate] Invalid media query: ${media}`, error);
        return false;
    }

    if (mql.matches) {
        stopMediaWatcher(element);
        return true;
    }

    if (mediaWatchers.has(element)) {
        return false;
    }

    const onChange = () => {
        if (!mql.matches) {
            return;
        }

        stopMediaWatcher(element);

        if (!element.isConnected) return;
        hydrateElement(element);
    };

    mediaWatchers.set(element, { mql, onChange });
    if (mql.addEventListener) {
        mql.addEventListener("change", onChange);
    } else {
        mql.addListener?.(onChange);
    }

    return false;
};

const cancelVisibilityWait = (element) => {
    visibilityWaiters.get(element)?.finish(false);
};

const waitForVisibility = (element) => {
    return new Promise((resolve) => {
        const observer = getVisibilityObserver();

        if (!observer) {
            resolve(true);
            return;
        }

        let settled = false;

        const finish = (visible) => {
            if (settled) {
                return;
            }

            settled = true;
            visibilityWaiters.delete(element);
            observer.unobserve(element);
            resolve(visible);
        };

        visibilityWaiters.set(element, { finish });
        observer.observe(element);
    });
};

/**
 * @example data-module="common/footer" → assets/common/footer/footer.js
 * @example data-module="heros/hero-homepage" → assets/heros/hero-homepage/hero-homepage.js
 * @example data-module="strates/strate-showcase" → assets/strates/strate-showcase/strate-showcase.js
 */
export const loadModule = (moduleName) => {
    if (!isValidModuleName(moduleName)) {
        console.error(`[Hydrate] Invalid module name: ${moduleName}`);
        return Promise.resolve(null);
    }

    if (moduleCache.has(moduleName)) {
        return Promise.resolve(moduleCache.get(moduleName));
    }

    if (pendingLoads.has(moduleName)) {
        return pendingLoads.get(moduleName);
    }

    const file = moduleName.split("/").pop();

    const promise = import(
        /* webpackChunkName: "[request]" */
        /* webpackInclude: /\/(common|components|heros|strates)\/[^/]+\/[^/]+\.js$/ */
        `../../${moduleName}/${file}.js`
    )
        .then((module) => {
            const constructor = module.default;
            moduleCache.set(moduleName, constructor);
            return constructor;
        })
        .catch((error) => {
            console.error(`[Hydrate] Failed to load module: ${moduleName}`, error);
            return null;
        })
        .finally(() => {
            pendingLoads.delete(moduleName);
        });

    pendingLoads.set(moduleName, promise);

    return promise;
};

const runLifecycleCallbacks = (callbacks, reverse = false) => {
    const orderedCallbacks = reverse ? [...callbacks].reverse() : callbacks;
    let firstError = null;

    for (const callback of orderedCallbacks) {
        try {
            callback();
        } catch (error) {
            firstError ??= error;
        }
    }

    if (firstError) throw firstError;
};

export const composeLifecycles = (instances) => {
    const lifecycles = (Array.isArray(instances) ? instances.flat(Infinity) : [instances])
        .filter(Boolean)
        .map((instance) => {
            if (typeof instance === "function") {
                return { destroy: instance, pause: null, resume: null };
            }

            if (typeof instance !== "object") return null;

            const bind = (name) => typeof instance[name] === "function"
                ? instance[name].bind(instance)
                : null;

            return {
                destroy: bind("destroy"),
                pause: bind("pause"),
                resume: bind("resume"),
            };
        })
        .filter(Boolean);

    const callbacks = (name) => lifecycles
        .map((lifecycle) => lifecycle[name])
        .filter(Boolean);

    const destroyCallbacks = callbacks("destroy");
    const pauseCallbacks = callbacks("pause");
    const resumeCallbacks = callbacks("resume");

    return {
        destroy: destroyCallbacks.length
            ? () => runLifecycleCallbacks(destroyCallbacks, true)
            : null,
        pause: pauseCallbacks.length
            ? () => runLifecycleCallbacks(pauseCallbacks)
            : null,
        resume: resumeCallbacks.length
            ? () => runLifecycleCallbacks(resumeCallbacks)
            : null,
    };
};

const stopLifecycleObserver = (element) => {
    lifecycleObserver?.unobserve(element);
    lifecycleEntries.delete(element);

    if (lifecycleEntries.size === 0) {
        lifecycleObserver?.disconnect();
        lifecycleObserver = null;
    }
};

const getLifecycleObserver = () => {
    if (lifecycleObserver || !("IntersectionObserver" in window)) {
        return lifecycleObserver;
    }

    lifecycleObserver = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            const lifecycle = lifecycleEntries.get(entry.target);
            if (!lifecycle) return;

            try {
                if (entry.isIntersecting) {
                    if (!lifecycle.isPaused) return;
                    lifecycle.resume();
                    lifecycle.isPaused = false;
                    return;
                }

                if (lifecycle.isPaused) return;
                lifecycle.pause();
                lifecycle.isPaused = true;
            } catch (error) {
                console.error("[Hydrate] Error updating module lifecycle", error);
            }
        });
    }, {
        root: null,
        rootMargin: "150px 0px",
        threshold: 0,
    });

    return lifecycleObserver;
};

const startLifecycleObserver = (element, lifecycle) => {
    const { pause, resume } = lifecycle;

    if (!pause || !resume) return;

    stopLifecycleObserver(element);

    const observer = getLifecycleObserver();
    if (!observer) return;

    lifecycleEntries.set(element, { pause, resume, isPaused: false });
    observer.observe(element);
};

const initModule = (element, ModuleConstructor, token, moduleName) => {
    if (!isCurrentHydration(element, token, moduleName)) return;

    if (!ModuleConstructor) {
        hydratedElements.set(element, { status: "error" });
        return;
    }

    try {
        const instance = ModuleConstructor(element);
        const lifecycle = composeLifecycles(instance);

        hydratedElements.set(element, {
            status: "loaded",
            ...lifecycle,
        });

        element.dataset.hydrated = "true";
        startLifecycleObserver(element, lifecycle);
    } catch (error) {
        console.error(`[Hydrate] Error initializing module: ${element.dataset.module}`, error);
        hydratedElements.set(element, { status: "error", error });
    }
};

const runHydration = (element, moduleName, token) => {
    const start = (shouldStart = true) => {
        if (!shouldStart || !isCurrentHydration(element, token, moduleName)) return;

        return loadModule(moduleName).then((ModuleConstructor) => {
            initModule(element, ModuleConstructor, token, moduleName);
        });
    };

    if (shouldWaitForVisibility(element)) {
        return waitForVisibility(element).then(start);
    }

    return start();
};

export const hydrateElement = (element) => {
    if (!element?.dataset || !element.isConnected) return;

    const existing = hydratedElements.get(element);

    if (existing && existing.status !== "error") {
        return;
    }

    const moduleName = element.dataset.module?.trim();

    if (!moduleName || !isValidModuleName(moduleName)) {
        if (moduleName) console.error(`[Hydrate] Invalid module name: ${moduleName}`);
        return;
    }

    if (!watchMediaCondition(element)) {
        return;
    }

    cancelVisibilityWait(element);
    const token = Symbol(moduleName);
    hydrationTokens.set(element, token);
    hydratedElements.set(element, { status: "pending", token });

    runHydration(element, moduleName, token);
};

export const hydrate = (container = document.documentElement) => {
    // L'IntersectionObserver de waitForVisibility() assure déjà le lazy load.
    // Tous les éléments peuvent donc être enregistrés immédiatement.
    getModuleElements(container).forEach(hydrateElement);
};

export const destroy = (element) => {
    if (!element?.dataset) return;

    const data = hydratedElements.get(element);

    hydrationTokens.delete(element);
    cancelVisibilityWait(element);
    stopLifecycleObserver(element);

    if (data?.destroy) {
        try {
            data.destroy();
        } catch (error) {
            console.error("[Hydrate] Error destroying module", error);
        }
    }

    stopMediaWatcher(element);

    hydratedElements.delete(element);
    delete element.dataset.hydrated;
};

export const destroyAll = (container = document.documentElement) => {
    getModuleElements(container).forEach(destroy);
};

export const observe = (container = document.body) => {
    const mutationObserver = new MutationObserver((mutations) => {
        const removedNodes = [];

        for (let i = 0; i < mutations.length; i++) {
            const mutation = mutations[i];

            for (let j = 0; j < mutation.addedNodes.length; j++) {
                const node = mutation.addedNodes[j];

                if (node.nodeType !== Node.ELEMENT_NODE) {
                    continue;
                }

                getModuleElements(node).forEach(hydrateElement);
            }

            for (let j = 0; j < mutation.removedNodes.length; j++) {
                const node = mutation.removedNodes[j];
                if (node.nodeType === Node.ELEMENT_NODE) removedNodes.push(node);
            }
        }

        // Un déplacement DOM apparaît comme remove + add dans le même lot.
        // Ne détruire que les arbres réellement détachés à la fin du callback.
        removedNodes.forEach((node) => {
            if (!node.isConnected) destroyAll(node);
        });
    });

    mutationObserver.observe(container, {
        childList: true,
        subtree: true,
    });

    return () => mutationObserver.disconnect();
};
