// Compatibility entry point. The builder itself finalizes all assets together.
const { readInventory, verifyInventory, planCleanup, removePlanned } = require('./asset-inventory');
function pruneOnDemandAssets(output, assetNames) {
    const inventory = assetNames ? null : verifyInventory(output, readInventory(output));
    const active = new Set(assetNames || inventory.files.map(entry => entry.name));
    const plan = planCleanup(output, active, inventory).filter(entry => entry.name.startsWith('css/'));
    return removePlanned(output, plan);
}
module.exports = { pruneOnDemandAssets };
