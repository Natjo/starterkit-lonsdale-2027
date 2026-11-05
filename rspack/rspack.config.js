const { validateThemeName } = require("./output-safety");
const themeName = validateThemeName();

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
    },
    theme_name: themeName,
    output_filename: "js/[name].js",
    assets_publicPath: `/wp-content/themes/${themeName}/assets/`,
    debug: process.env.NODE_ENV === "development",
};

module.exports = config;
