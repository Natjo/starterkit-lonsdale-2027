const fs = require('node:fs');
const path = require('node:path');
const { randomUUID } = require('node:crypto');
const { safePath } = require('./output-safety');

/** One lock per output, shared by every compiler and held for the whole watch. */
class BuildOutputLock {
    constructor() {
        this.running = new Set();
        this.token = randomUUID();
        this.onExit = () => this.release();
        this.onSignal = signal => {
            this.release();
            process.exit(signal === 'SIGINT' ? 130 : 143);
        };
        this.onInterrupt = () => this.onSignal('SIGINT');
        this.onTerminate = () => this.onSignal('SIGTERM');
    }
    acquire(output, name) {
        if (this.directory) {
            if (this.output !== path.resolve(output)) throw new Error('Les compilateurs doivent partager la même sortie.');
            this.running.add(name);
            return;
        }
        fs.mkdirSync(output, { recursive: true });
        safePath(output);
        const directory = safePath(output, '.rspack-build.lock', { missing: true });
        try { fs.mkdirSync(directory, { mode: 0o700 }); }
        catch (error) {
            if (error.code !== 'EEXIST') throw error;
            throw new Error(`Une compilation ou un watcher utilise déjà ${output}. Arrêter le watcher avant de compiler. Si le processus a été tué brutalement, vérifier qu’aucun build ne tourne avant de retirer ${directory}.`);
        }
        try { fs.writeFileSync(path.join(directory, 'owner.json'), JSON.stringify({ pid: process.pid, token: this.token }), { flag: 'wx', mode: 0o600 }); }
        catch (error) { fs.rmdirSync(directory); throw error; }
        this.directory = directory;
        this.output = path.resolve(output);
        this.running.add(name);
        process.once('exit', this.onExit);
        process.once('SIGINT', this.onInterrupt);
        process.once('SIGTERM', this.onTerminate);
    }
    close(name) {
        this.running.delete(name);
        if (this.running.size === 0) this.release();
    }
    release() {
        if (!this.directory) return;
        const directory = this.directory;
        this.directory = null;
        this.running.clear();
        process.removeListener('exit', this.onExit);
        process.removeListener('SIGINT', this.onInterrupt);
        process.removeListener('SIGTERM', this.onTerminate);
        try {
            const ownerFile = safePath(this.output, '.rspack-build.lock/owner.json');
            const owner = JSON.parse(fs.readFileSync(ownerFile, 'utf8'));
            if (owner.token !== this.token) return;
            fs.unlinkSync(ownerFile);
            fs.rmdirSync(directory);
        } catch (error) {
            if (error.code !== 'ENOENT') console.error(`Impossible de libérer le verrou Rspack : ${error.message}`);
        }
    }
}

class BuildLockPlugin {
    constructor(lock) { this.lock = lock; }
    apply(compiler) {
        const acquire = () => this.lock.acquire(compiler.options.output.path, compiler.name);
        compiler.hooks.beforeRun.tap('BuildLockPlugin', acquire);
        compiler.hooks.watchRun.tap('BuildLockPlugin', acquire);
        compiler.hooks.shutdown.tap('BuildLockPlugin', () => this.lock.close(compiler.name));
    }
}
module.exports = { BuildOutputLock, BuildLockPlugin };
