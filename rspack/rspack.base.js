const config = require("./rspack.config");
const path = require("path");
const rspack = require("@rspack/core");
const {
    APP_SCSS,
    getOnceEntries,
    getSplitEntries,
} = require("./parse-split-imports");

const isDev = config.debug;
const sassDispatchLoader = path.resolve(__dirname, "loaders/sass-dispatch-loader.js");
const sassDispatchUtils = path.resolve(__dirname, "sass-dispatch-utils.js");
const parseSplitImports = path.resolve(__dirname, "parse-split-imports.js");
const svgoLoader = path.resolve(__dirname, "loaders/svgo-loader.js");
const optimizeSvgUtils = path.resolve(__dirname, "utils/optimize-svg.js");
const { optimizeSvgCopyTransform } = require("./utils/optimize-svg");
const { pruneOnDemandAssets } = require("./prune-on-demand-assets");
const { ChunkCleanupCoordinator, PruneChunksPlugin } = require("./prune-chunks");
const assetsRoot = path.resolve(__dirname, "../assets");
const copyIgnoredFiles = ["**/.DS_Store", "**/.gitkeep"];

class WatchDynamicEntriesPlugin {
    apply(compiler) {
        compiler.hooks.afterCompile.tap(
            "WatchDynamicEntriesPlugin",
            (compilation) => compilation.fileDependencies.add(APP_SCSS),
        );
    }
}

class PruneOnDemandAssetsPlugin {
    apply(compiler) {
        compiler.hooks.afterEmit.tap("PruneOnDemandAssetsPlugin", (compilation) => {
            const removed = pruneOnDemandAssets(compiler.options.output.path);

            if (removed.length > 0) {
                compilation.getLogger("PruneOnDemandAssetsPlugin").info(
                    `Removed ${removed.length} orphan on-demand asset(s).`,
                );
            }
        });
    }
}

const createScssUse = (withDispatch = false) => [
    rspack.CssExtractRspackPlugin.loader,
    {
        loader: "css-loader",
        options: {
            sourceMap: isDev,
            importLoaders: withDispatch ? 3 : 2,
        },
    },
    {
        loader: "postcss-loader",
        options: {
            postcssOptions: {
                plugins: [["autoprefixer"]],
            },
        },
    },
    {
        loader: "sass-loader",
        options: {
            sourceMap: isDev,
            sassOptions: {
                indentWidth: 4,
                outputStyle: isDev ? "expanded" : "compressed",
                quietDeps: true,
                loadPaths: [assetsRoot],
                silenceDeprecations: ["import", "legacy-js-api"],
            },
        },
    },
    ...(withDispatch
        ? [
              {
                  loader: sassDispatchLoader,
              },
          ]
        : []),
];

/**
 * @param {{
 *   name: string,
 *   entry: Record<string, string | string[]>,
 *   clean?: boolean,
 *   withCopy?: boolean,
 *   watchDynamicEntries?: boolean,
 * }} options
 */
function createRspackConfig({
    name,
    entry,
    withCopy = false,
    watchDynamicEntries = false,
}) {
    return {
        name,
        devtool: isDev ? "eval-cheap-module-source-map" : false,
        entry,
        output: {
            path: path.join(
                __dirname,
                "./../web/wp-content/themes",
                config.theme_name,
                "assets",
            ),
            filename: config.output_filename,
            publicPath: config.assets_publicPath,
            chunkFilename: "./js/chunks/[contenthash:8].js",
            clean: false,
            environment: {
                arrowFunction: true,
                bigIntLiteral: true,
                const: true,
                destructuring: true,
                dynamicImport: true,
                forOf: true,
                module: true,
                optionalChaining: true,
                templateLiteral: true,
            },
        },
        resolve: {
            extensions: [".js", ".mjs", ".css"],
            alias: {
                "@modules": path.resolve(__dirname, "../assets/modules"),
                "@strates": path.resolve(__dirname, "../assets/strates"),
                "@common": path.resolve(__dirname, "../assets/common"),
                "@components": path.resolve(__dirname, "../assets/components"),
                "@fonts": path.resolve(__dirname, "../assets/fonts"),
                "@img": path.resolve(__dirname, "../assets/img"),
                "@assets/vendors": path.resolve(__dirname, "../assets/vendors"),
                "@": assetsRoot,
            },
        },
        cache: isDev
            ? {
                  type: "persistent",
                  buildDependencies: [
                      __filename,
                      path.join(__dirname, "rspack.config.js"),
                      path.join(__dirname, "rspack.dev.js"),
                      path.join(__dirname, "rspack.prod.js"),
                      sassDispatchLoader,
                      sassDispatchUtils,
                      parseSplitImports,
                      svgoLoader,
                      optimizeSvgUtils,
                      APP_SCSS,
                  ],
                  storage: {
                      type: "filesystem",
                  },
              }
            : false,
        module: {
            rules: [
                {
                    test: /\.m?js$/,
                    exclude: [
                        /node_modules/,
                        path.resolve(__dirname, "../assets/vendors"),
                    ],
                    loader: "builtin:swc-loader",
                    options: {
                        jsc: {
                            parser: { syntax: "ecmascript" },
                            target: "es2020",
                        },
                    },
                },
                {
                    test: /\.css$/,
                    use: [
                        rspack.CssExtractRspackPlugin.loader,
                        {
                            loader: "css-loader",
                            options: {
                                sourceMap: isDev,
                                importLoaders: 1,
                            },
                        },
                        {
                            loader: "postcss-loader",
                            options: {
                                postcssOptions: {
                                    plugins: [["autoprefixer"]],
                                },
                            },
                        },
                    ],
                },
                {
                    oneOf: [
                        {
                            test: /app\.scss$/,
                            use: createScssUse(true),
                        },
                        {
                            resourceQuery: /dispatch=split/,
                            use: createScssUse(true),
                        },
                        {
                            test: /\.(sass|scss)$/,
                            use: createScssUse(),
                        },
                    ],
                },
                {
                    test: /\.(woff2?)(\?.*)?$/,
                    type: "asset",
                    parser: {
                        dataUrlCondition: { maxSize: 8 * 1024 },
                    },
                    generator: {
                        filename: "fonts/[name][ext]",
                    },
                },
                {
                    test: /\.(eot|ttf)(\?.*)?$/,
                    type: "asset/resource",
                    generator: {
                        filename: "fonts/[name][ext]",
                    },
                },
                {
                    test: /\.(mp4|ogv|webm)(\?.*)?$/,
                    type: "asset/resource",
                    generator: {
                        filename: "video/[name][ext]",
                    },
                },
                {
                    test: /\.(png|jpe?g|gif|webp|avif)(\?.*)?$/,
                    type: "asset",
                    parser: {
                        dataUrlCondition: { maxSize: 4 * 1024 },
                    },
                    generator: {
                        filename: "img/[name][ext]",
                    },
                },
                {
                    test: /\.svg(\?.*)?$/,
                    ...(!isDev
                        ? {
                              use: [{ loader: svgoLoader }],
                          }
                        : {}),
                    type: "asset/resource",
                    generator: {
                        filename: "img/[name][ext]",
                    },
                },
                {
                    test: /\.(ico|webmanifest)(\?.*)?$/,
                    type: "asset/resource",
                    generator: {
                        filename: "img/[name][ext]",
                    },
                },
            ],
        },
        plugins: [
            new rspack.CssExtractRspackPlugin({
                filename: "css/[name].css",
            }),
            ...(name === "app" ? [new PruneOnDemandAssetsPlugin()] : []),
            ...(watchDynamicEntries ? [new WatchDynamicEntriesPlugin()] : []),
            ...(withCopy
                ? [
                      new rspack.CopyRspackPlugin({
                          patterns: [
                              {
                                  from: "./assets/fonts",
                                  to: "fonts",
                                  noErrorOnMissing: true,
                                  globOptions: {
                                      ignore: copyIgnoredFiles,
                                  },
                              },
                              {
                                  from: "./assets/video",
                                  to: "video",
                                  noErrorOnMissing: true,
                                  globOptions: {
                                      ignore: copyIgnoredFiles,
                                  },
                              },
                              {
                                  from: "./assets/favicon",
                                  to: "favicon",
                                  noErrorOnMissing: true,
                                  globOptions: {
                                      ignore: [
                                          ...copyIgnoredFiles,
                                          "**/head-snippet.html",
                                      ],
                                  },
                                  ...(!isDev
                                      ? { transform: optimizeSvgCopyTransform }
                                      : {}),
                              },
                              {
                                  from: "./assets/img",
                                  to: "img",
                                  noErrorOnMissing: true,
                                  globOptions: {
                                      ignore: copyIgnoredFiles,
                                  },
                                  ...(!isDev
                                      ? { transform: optimizeSvgCopyTransform }
                                      : {}),
                              },
                              {
                                  from: "./assets/components/lottie/*.json",
                                  to: "lottie/[name][ext]",
                                  noErrorOnMissing: true,
                              },
                          ],
                      }),
                  ]
                : []),
        ],
        optimization: {
            moduleIds: "deterministic",
            chunkIds: "deterministic",
            usedExports: true,
            sideEffects: true,
            splitChunks: {
                chunks: "async",
            },
        },
        performance: {
            hints: isDev ? false : "warning",
            maxEntrypointSize: 400000,
            maxAssetSize: 400000,
        },
        stats: isDev ? "minimal" : "normal",
    };
}

function createRspackConfigs() {
    const configs = [
        createRspackConfig({
            name: "critical",
            entry: config.entries.critical,
        }),
        createRspackConfig({
            name: "app",
            entry: config.entries.app,
            withCopy: true,
        }),
        createRspackConfig({
            name: "admin",
            entry: config.entries.admin,
        }),
    ];

    const getDynamicEntries = () => ({
        ...getSplitEntries(),
        ...getOnceEntries(),
    });
    const splitEntries = getDynamicEntries();

    if (splitEntries && Object.keys(splitEntries).length > 0) {
        configs.push(
            createRspackConfig({
                name: "split",
                entry: isDev ? getDynamicEntries : splitEntries,
                watchDynamicEntries: isDev,
            }),
        );
    }

    const chunks = new ChunkCleanupCoordinator(configs.map(compiler => compiler.name));
    for (const compiler of configs) compiler.plugins.push(new PruneChunksPlugin(chunks, compiler.name));
    return configs;
}

module.exports = {
    createRspackConfig,
    createRspackConfigs,
    isDev,
};
