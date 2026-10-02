import { hydrate, observe } from "@modules/hydrate/hydrate";
import { initLenis } from "@modules/lenis/lenis";
import HeaderNav from "@common/header-nav/header-nav";
import HeaderNavComplex from "@common/header-nav-complex/header-nav-complex";

HeaderNav();
HeaderNavComplex(); // @common/header-nav-complex/header-nav-complex
hydrate();
observe();

initLenis().catch((error) => {
    console.error("[Lenis] Failed to initialize", error);
});
