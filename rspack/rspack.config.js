const { getOnceEntries, getSplitEntries } = require("./parse-split-imports");

if (!process.env.WP_THEME_NAME) {
    throw new Error("WP_THEME_NAME is required (e.g. WP_THEME_NAME=lonsdale-richard npm run prod)");
}

const splitEntries = {
    ...getSplitEntries(),
    ...getOnceEntries(),
};

const config = {
    entries: {
        critical: {
            critical: "./assets/app.scss?dispatch=critical",
        },
        app: {
            app: ["./assets/app.scss?dispatch=app", "./assets/app.js"],
        },
        admin: {
            admin: ["./assets/styles/utils/admin.scss"],
            "admin-rte": ["./assets/styles/utils/admin-rte.scss"],
        },
        split: splitEntries,
    },
    domain: process.env.DOMAIN,
    proxy: `https://${process.env.DOMAIN}/`,
    port: process.env.NODE_PORT,
    theme_name: process.env.WP_THEME_NAME,
    output_filename: "js/[name].js",
    assets_publicPath: `/wp-content/themes/${process.env.WP_THEME_NAME}/assets/`,
    debug: process.env.NODE_ENV === "development",
};

module.exports = config;
