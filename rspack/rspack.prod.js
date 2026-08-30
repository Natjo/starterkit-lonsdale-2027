const rspack = require("@rspack/core");
const { createRspackConfigs } = require("./rspack.base.js");

const prodOptimization = {
    minimize: true,
    minimizer: [
        new rspack.SwcJsMinimizerRspackPlugin({
            extractComments: false,
            minimizerOptions: {
                compress: {
                    ecma: 2020,
                    comparisons: false,
                    inline: 2,
                    drop_console: true,
                    drop_debugger: true,
                    pure_funcs: ["console.log", "console.info", "console.debug"],
                    passes: 2,
                },
                mangle: true,
                format: {
                    ecma: 2020,
                    comments: false,
                    ascii_only: true,
                },
            },
        }),
        new rspack.LightningCssMinimizerRspackPlugin(),
    ],
    concatenateModules: true,
};

module.exports = createRspackConfigs().map((compilerConfig) => ({
    ...compilerConfig,
    mode: "production",
    optimization: {
        ...compilerConfig.optimization,
        ...prodOptimization,
    },
}));
