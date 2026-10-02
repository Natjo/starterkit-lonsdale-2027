import { hydrate, observe } from "@modules/hydrate/hydrate";
import { initLenis } from "@modules/lenis/lenis";
import HeaderNav from "@common/header-nav/header-nav";
import HeaderNavSubnav from "@common/header-nav-subnav/header-nav-subnav";
import HeaderNavDropdown from "@common/header-nav-dropdown/header-nav-dropdown";
import HeaderNavMega from "@common/header-nav-mega/header-nav-mega";

HeaderNav();
HeaderNavSubnav(); // @common/header-nav-subnav/header-nav-subnav
HeaderNavDropdown(); // @common/header-nav-dropdown/header-nav-dropdown
HeaderNavMega(); // @common/header-nav-mega/header-nav-mega
hydrate();
observe();

initLenis().catch((error) => {
    console.error("[Lenis] Failed to initialize", error);
});
