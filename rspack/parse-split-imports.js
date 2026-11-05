const fs = require('node:fs');
const path = require('node:path');
const { parseAppImports, isHeroCritical, HERO_PREFIX } = require('./sass-imports');
const APP_SCSS = path.resolve(__dirname, '../assets/app.scss');
const isOnDemand = item => item.flags.includes('split') || item.flags.includes('once') || isHeroCritical(item.path, item.flags);
const parseOnDemandImports = source => parseAppImports(source).filter(isOnDemand).map(item => item.path);
const parseOnceImports = source => parseAppImports(source).filter(item => item.flags.includes('once')).map(item => item.path);

function getDynamicEntries() {
    const source = fs.readFileSync(APP_SCSS, 'utf8');
    return Object.fromEntries(parseAppImports(source).filter(isOnDemand).map(item => [
        `${item.flags.includes('once') ? 'once/' : ''}${item.path}`,
        `./assets/${item.path}.scss?dispatch=split`,
    ]));
}
const getSplitEntries = () => Object.fromEntries(Object.entries(getDynamicEntries()).filter(([name]) => !name.startsWith('once/')));
const getOnceEntries = () => Object.fromEntries(Object.entries(getDynamicEntries()).filter(([name]) => name.startsWith('once/')));
module.exports = {
    APP_SCSS, getDynamicEntries, getSplitEntries, getOnceEntries,
    parseOnDemandImports, parseOnceImports, parseSplitImports: parseOnDemandImports,
    isHeroCritical, HERO_PREFIX,
};
