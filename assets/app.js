import { hydrate, observe } from "@modules/hydrate/hydrate";
import { initLenis } from "@modules/lenis/lenis";
import HeaderNav from "@common/header-nav/header-nav";
import HeaderNavSubnav from "@common/header-nav-subnav/header-nav-subnav";
import HeaderNavDropdown from "@common/header-nav-dropdown/header-nav-dropdown";
import HeaderNavMega from "@common/header-nav-mega/header-nav-mega";

import HeaderNavCta from "@common/header-nav-cta/header-nav-cta";
import HeaderNavMinimal from "@common/header-nav-minimal/header-nav-minimal";
import HeaderNavCentered from "@common/header-nav-centered/header-nav-centered";

import HeaderNavSearch from "@common/header-nav-search/header-nav-search";
import HeaderNavTopbar from "@common/header-nav-topbar/header-nav-topbar";
import HeaderNavCommerce from "@common/header-nav-commerce/header-nav-commerce";

HeaderNav();
HeaderNavSubnav(); // @common/header-nav-subnav/header-nav-subnav
HeaderNavDropdown(); // @common/header-nav-dropdown/header-nav-dropdown
HeaderNavMega(); // @common/header-nav-mega/header-nav-mega
HeaderNavCta(); // @common/header-nav-cta/header-nav-cta
HeaderNavMinimal(); // @common/header-nav-minimal/header-nav-minimal
HeaderNavCentered(); // @common/header-nav-centered/header-nav-centered
HeaderNavSearch(); // @common/header-nav-search/header-nav-search
HeaderNavTopbar(); // @common/header-nav-topbar/header-nav-topbar
HeaderNavCommerce(); // @common/header-nav-commerce/header-nav-commerce
hydrate();
observe();

initLenis().catch((error) => {
    console.error("[Lenis] Failed to initialize", error);
});
