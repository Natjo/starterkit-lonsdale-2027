const { optimize } = require("svgo");

const SPRITE_PLUGINS = [
    "cleanupAttrs",
    "removeDoctype",
    "removeXMLProcInst",
    "removeComments",
    "removeMetadata",
    "removeEditorsNSData",
    "cleanupNumericValues",
    "convertColors",
    "sortAttrs",
    {
        name: "removeAttrs",
        params: {
            attrs: "(data-name)",
        },
    },
];

const isSvgSpriteSource = source => source.includes("<symbol");

/**
 * @param {string} source
 * @param {string} [filePath]
 * @returns {string}
 */
function optimizeSvg(source, filePath = "") {
    const result = optimize(source, {
        path: filePath,
        plugins: isSvgSpriteSource(source) ? SPRITE_PLUGINS : undefined,
    });

    return result.data;
}

/**
 * CopyRspackPlugin transform
 * @param {Buffer} content
 * @param {string} absolutePath
 * @returns {Buffer}
 */
function optimizeSvgCopyTransform(content, absolutePath) {
    if (!/\.svg$/i.test(absolutePath)) {
        return content;
    }

    return Buffer.from(
        optimizeSvg(content.toString("utf8"), absolutePath),
        "utf8",
    );
}

module.exports = {
    optimizeSvg,
    optimizeSvgCopyTransform,
};
