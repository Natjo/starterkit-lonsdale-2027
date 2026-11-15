"""Publish only the generated Design System, using an isolated Git checkout."""
import argparse
import json
import os
import pathlib
import re
import shutil
import subprocess
import sys
import tempfile

ROOT = pathlib.Path(__file__).resolve().parents[1]
CONFIG = pathlib.Path(__file__).with_name('config.json')


def publication_config():
    try:
        value = json.loads(CONFIG.read_text())
    except (OSError, ValueError) as error:
        raise RuntimeError('Configuration design-system/config.json invalide : '+str(error))
    if not isinstance(value, dict):
        raise RuntimeError('La configuration du Design System doit être un objet JSON.')
    repository = value.get('repository', '')
    branch = value.get('branch', 'main')
    if not isinstance(repository, str) or not repository.startswith(('https://', 'git@')) or any(char.isspace() for char in repository):
        raise RuntimeError('Le dépôt DS doit être une URL Git HTTPS ou SSH.')
    if not isinstance(branch, str) or not branch or branch.startswith(('-', '/')):
        raise RuntimeError('Branche de publication invalide.')
    result = subprocess.run(['git', 'check-ref-format', '--branch', branch], capture_output=True)
    if result.returncode:
        raise RuntimeError('Branche de publication invalide.')
    return repository, branch
MANAGED = ('index.html', 'manifest.json', 'design-system', 'wp-content', '.nojekyll',
           'styles', 'components', 'modules', 'cards', 'strates', 'heros', 'common', 'assets')


def git(directory, *arguments, capture=False):
    result = subprocess.run(['git', '-c', 'core.hooksPath=/dev/null', *arguments], cwd=directory,
                            check=True, text=True, stdout=subprocess.PIPE if capture else None)
    return result.stdout.strip() if capture else ''


def local_url():
    values = {}
    for file in (ROOT/'.docker/.env', ROOT/'.env'):
        if not file.is_file():
            continue
        for line in file.read_text().splitlines():
            match = re.match(r'^(PROJECT_NAME|APP_NAME|DOMAIN)=(.*)$', line.strip())
            if match:
                values[match[1]] = match[2].strip().strip('\"\'')
    domain = values.get('DOMAIN', '')
    for key, value in values.items():
        domain = domain.replace('${'+key+'}', value)
    if not domain or '$' in domain:
        raise RuntimeError('Domaine local introuvable : utilisez URL=https://votre-starter.code')
    return 'https://' + domain


def pages_prefix(repository):
    match = re.fullmatch(r'(?:https://github\.com/|git@github\.com:)[^/]+/([^/]+?)(?:\.git)?', repository)
    if not match:
        return ''
    name = match[1]
    return '' if name.lower().endswith('.github.io') else '/' + name


def prepare_pages(checkout, prefix):
    # Flatten the virtual-route prefix only in the published copy.
    virtual = checkout/'design-system'
    if virtual.is_dir():
        for child in virtual.iterdir():
            if child.name not in MANAGED:
                raise RuntimeError('Rubrique d’export inconnue : '+child.name)
            shutil.move(str(child), str(checkout/child.name))
        virtual.rmdir()
    for name in MANAGED:
        path = checkout/name
        files = path.rglob('*') if path.is_dir() else [path]
        for file in files:
            if not file.is_file() or file.suffix not in ('.html', '.css', '.js', '.mjs'):
                continue
            text = file.read_text()
            text = re.sub(r'(?<![A-Za-z0-9_./-])/(design-system|wp-content)/',
                          lambda match: prefix + ('/' if match[1] == 'design-system' else match[0]), text)
            file.write_text(text)
    manifest = checkout/'manifest.json'
    data = json.loads(manifest.read_text())
    data['basePath'] = prefix
    data['entry'] = prefix + '/components/'
    manifest.write_text(json.dumps(data))
    (checkout/'.nojekyll').write_text('')


def publish(export, repository=None, identity_root=ROOT, base_path=None, branch="main"):
    if repository is None:
        repository, branch = publication_config()
    if not (export/'manifest.json').is_file() or not (export/'design-system/components/index.html').is_file():
        raise RuntimeError('Export incomplet ; publication annulée.')
    if any(path.is_symlink() for path in export.rglob('*')):
        raise RuntimeError('Lien symbolique dans l’export ; publication annulée.')
    with tempfile.TemporaryDirectory(prefix='design-system-publish-') as temporary:
        checkout = pathlib.Path(temporary)
        git(checkout, 'init', '--quiet', '--initial-branch='+branch)
        git(checkout, 'remote', 'add', 'origin', repository)
        heads = git(checkout, 'ls-remote', '--heads', 'origin', capture=True)
        if any(line.endswith('\trefs/heads/'+branch) for line in heads.splitlines()):
            git(checkout, 'fetch', '--depth=1', 'origin', branch)
            git(checkout, 'checkout', '-B', branch, 'FETCH_HEAD')
        # A new publication branch starts with an empty history, never the starter tree.
        # Use the developer’s existing identity; never invent an author.
        for key in ('user.name', 'user.email'):
            value = subprocess.run(['git', 'config', '--get', key], cwd=identity_root,
                                   text=True, capture_output=True)
            if value.returncode == 0:
                git(checkout, 'config', key, value.stdout.strip())
        for name in MANAGED:
            destination = checkout/name
            if destination.is_symlink():
                raise RuntimeError('Destination Git inattendue : '+name)
            if destination.is_dir():
                shutil.rmtree(destination)
            elif destination.exists():
                destination.unlink()
            source = export/name
            if source.is_dir():
                shutil.copytree(source, destination)
            elif source.is_file():
                shutil.copyfile(source, destination)
        prepare_pages(checkout, pages_prefix(repository) if base_path is None else base_path)
        git(checkout, 'add', '--all')
        changed = subprocess.run(['git', 'diff', '--cached', '--quiet'], cwd=checkout)
        if changed.returncode == 0:
            print('Design System déjà à jour : aucun commit ni push.')
            return False
        if changed.returncode != 1:
            raise RuntimeError('Impossible de vérifier les changements de l’export.')
        git(checkout, 'commit', '-m', 'Update Design System')
        # A concurrent publication rejects this push normally; never force.
        git(checkout, 'push', 'origin', 'HEAD:refs/heads/'+branch)
        print('Design System publié sur '+branch+'.')
        return True


def main():
    repository, branch = publication_config()
    parser = argparse.ArgumentParser()
    parser.add_argument('--url', default='')
    parser.add_argument('--repository', default=os.environ.get('DESIGN_SYSTEM_REPOSITORY', repository))
    parser.add_argument('--branch', default=branch)
    args = parser.parse_args()
    subprocess.run([sys.executable, str(ROOT/'design-system/export.py'),
                    args.url or local_url(), str(ROOT/'design-system/dist')], check=True)
    publish(ROOT/'design-system/dist', args.repository, branch=args.branch)


if __name__ == '__main__':
    try:
        main()
    except (RuntimeError, subprocess.CalledProcessError) as error:
        raise SystemExit('Publication interrompue : '+str(error))
