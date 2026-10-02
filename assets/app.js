import { hydrate, observe } from "@modules/hydrate/hydrate";
import { initLenis } from "@modules/lenis/lenis";
import HeaderNav from "@common/header-nav/header-nav";
import HeaderNavSubnav from "@common/header-nav-subnav/header-nav-subnav";

HeaderNav();
HeaderNavSubnav(); // @common/header-nav-subnav/header-nav-subnav
hydrate();
observe();

initLenis().catch((error) => {
    console.error("[Lenis] Failed to initialize", error);
});
