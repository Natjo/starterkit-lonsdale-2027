import { hydrate, observe } from "@modules/hydrate/hydrate";
import { initLenis } from "@modules/lenis/lenis";
import HeaderNav from "@common/header-nav/header-nav";

HeaderNav();
hydrate();
observe();

initLenis().catch((error) => {
    console.error("[Lenis] Failed to initialize", error);
});
