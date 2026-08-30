const { optimizeSvg } = require("../utils/optimize-svg");

module.exports = function svgoLoader(source) {
    return optimizeSvg(source, this.resourcePath);
};
