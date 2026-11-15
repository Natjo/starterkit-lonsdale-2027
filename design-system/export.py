"""Export the local DS without exporting WordPress, its database or private uploads."""
import json, pathlib, re, shutil, subprocess, sys, urllib.request, urllib.parse, urllib.error, ssl
from local_tls import local_context
root = pathlib.Path(__file__).resolve().parents[1]
base = sys.argv[1].rstrip('/')
out = pathlib.Path(sys.argv[2]).resolve()
u = urllib.parse.urlsplit(base)
if u.scheme not in ('http','https') or not (u.hostname in ('localhost','127.0.0.1') or (u.hostname or '').endswith('.code')):
    raise SystemExit('URL locale requise : make design-system-export URL=https://starter.code')
context = local_context(root, u.hostname) if u.scheme == 'https' else None
if out != root / 'design-system/dist': raise SystemExit('Destination réservée à design-system/dist')
staging = root / 'design-system/dist.next'
if staging.exists(): shutil.rmtree(staging)
staging.mkdir()
try:
    themes = [theme for theme in (root/'web/wp-content/themes').iterdir() if (theme/'template-parts/components').is_dir()]
    if len(themes) != 1: raise RuntimeError('Un seul thème attendu pour exporter le Design System.')
    placeholder = '/wp-content/themes/' + themes[0].name + '/assets/img/placeholders/429-1240x800.jpg'
    for family in ('styles','components','modules','cards','strates','heros','common'):
        address = base + '/design-system/' + family + '/'
        response = urllib.request.urlopen(address, timeout=30, context=context)
        if response.geturl().split('?')[0].rstrip('/') != address.rstrip('/'): raise RuntimeError('Redirection inattendue : '+address)
        html = response.read().decode('utf-8').replace(base, '')
        html = re.sub(r'/wp-content/uploads/[^\s\"<>]+', placeholder, html)
        # The static export has no PHP endpoint for partial common renders.
        if family == 'common':
            html = re.sub(r'(<(?:select|input|button)\b)(?![^>]*\bdisabled)', r'\1 disabled', html)
            html = html.replace('data-common-choices', 'data-static-common-choices')
        path = staging / 'design-system' / family / 'index.html'
        path.parent.mkdir(parents=True, exist_ok=True); path.write_text(html)
    module = staging/'design-system/assets'; module.mkdir(parents=True)
    for file in pathlib.Path(__file__).parent.iterdir():
        if file.is_file() and file.suffix in ('.js','.css','.jpg','.png','.svg'): shutil.copyfile(file,module/file.name)
    for theme in (root/'web/wp-content/themes').iterdir():
        assets = theme/'assets'
        if assets.is_dir(): shutil.copytree(assets,staging/'wp-content/themes'/theme.name/'assets')
    for file in staging.rglob('*'):
        if file.suffix in ('.css','.js') and file.is_file(): file.write_text(file.read_text().replace(base,''))
    (staging/'index.html').write_text('<meta http-equiv="refresh" content="0;url=/design-system/components/">')
    commit = subprocess.check_output(['git','rev-parse','HEAD'],cwd=root,text=True).strip()
    (staging/'manifest.json').write_text(json.dumps({'version':1,'sourceCommit':commit,'entry':'/design-system/components/'}))
    if out.exists(): shutil.rmtree(out)
    staging.rename(out)
except urllib.error.URLError as error:
    shutil.rmtree(staging,ignore_errors=True)
    if isinstance(error.reason, ssl.SSLCertVerificationError):
        raise SystemExit('Certificat HTTPS local non reconnu : vérifiez .docker/certs/' + u.hostname + '.crt ou fournissez la CA avec SSL_CERT_FILE.')
    raise SystemExit('Impossible de lire le Design System local : ' + str(error.reason))
except BaseException:
    shutil.rmtree(staging,ignore_errors=True); raise
print('Export prêt :',out)
