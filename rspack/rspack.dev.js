const rspack = require("@rspack/core");
const { createRspackConfigs } = require("./rspack.base");

const devOptimization = {
    minimize: true,
    minimizer: [
        new rspack.SwcJsMinimizerRspackPlugin({
            extractComments: false,
            minimizerOptions: {
                compress: {
                    ecma: 2020,
                    comparisons: false,
                    inline: 2,
                    drop_console: false,
                    drop_debugger: false,
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
    removeAvailableModules: false,
    removeEmptyChunks: false,
    splitChunks: false,
    runtimeChunk: false,
};

module.exports = createRspackConfigs().map((compilerConfig) => ({
    ...compilerConfig,
    mode: "development",
    devtool: "source-map",
    optimization: {
        ...compilerConfig.optimization,
        ...devOptimization,
    },
    watchOptions: {
        ignored: /node_modules/,
        aggregateTimeout: 200,
    },
}));
