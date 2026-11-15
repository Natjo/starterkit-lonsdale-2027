"""Publication contract against a temporary local remote, never GitHub."""
import importlib.util
import pathlib
import subprocess
import tempfile

spec=importlib.util.spec_from_file_location('ds_publish',pathlib.Path(__file__).with_name('publish.py'))
module=importlib.util.module_from_spec(spec);spec.loader.exec_module(module)
def git(path,*args):
    return subprocess.check_output(['git','-c','core.hooksPath=/dev/null',*args],cwd=path,text=True).strip()
with tempfile.TemporaryDirectory(prefix='test-ds-publish-') as temporary:
    root=pathlib.Path(temporary)
    remote=root/'remote.git';remote.mkdir();git(remote,'init','--bare','--initial-branch=main')
    identity=root/'identity';identity.mkdir();git(identity,'init','--initial-branch=main')
    git(identity,'config','user.name','DS test');git(identity,'config','user.email','ds@example.invalid')
    export=root/'export'
    (export/'design-system/components').mkdir(parents=True)
    (export/'wp-content').mkdir()
    (export/'index.html').write_text('<meta http-equiv="refresh" content="0;url=/design-system/components/">')
    (export/'manifest.json').write_text('{"version":1}')
    (export/'design-system/components/index.html').write_text('<link href="/wp-content/style.css"><a href="/design-system/styles/">Styles</a>')
    (export/'wp-content/style.css').write_text('style')
    assert module.publish(export,str(remote),identity,base_path='/Design-system')
    assert '/Design-system/components/' in git(remote,'show','main:index.html')
    assert '/Design-system/wp-content/style.css' in git(remote,'show','main:components/index.html')
    assert '/Design-system/styles/' in git(remote,'show','main:components/index.html')
    assert not any(path.startswith('design-system/') for path in git(remote,'ls-tree','-r','--name-only','main').splitlines())
    assert git(remote,'show','main:.nojekyll')==''
    first=git(remote,'rev-parse','main')
    assert not module.publish(export,str(remote),identity,base_path='/Design-system')
    assert first==git(remote,'rev-parse','main')
    checkout=root/'other';git(root,'clone',str(remote),str(checkout))
    git(checkout,'config','user.name','DS test');git(checkout,'config','user.email','ds@example.invalid')
    (checkout/'README.md').write_text('Keep this documentation')
    (checkout/'wp-content/obsolete.css').write_text('stale')
    git(checkout,'add','.');git(checkout,'commit','-m','Add unrelated documentation and old asset');git(checkout,'push')
    (export/'wp-content/style.css').write_text('updated style')
    assert module.publish(export,str(remote),identity,base_path='/Design-system')
    assert git(remote,'show','main:README.md')=='Keep this documentation'
    assert 'obsolete.css' not in git(remote,'ls-tree','-r','--name-only','main')
    assert git(identity,'status','--porcelain')==''
    assert module.publish(export,str(remote),identity,base_path='/starterkit-lonsdale-2027',branch='design-sytem')
    published=set(git(remote,'ls-tree','-r','--name-only','design-sytem').splitlines())
    assert 'components/index.html' in published
    assert 'README.md' not in published
    assert git(remote,'rev-list','--count','design-sytem')=='1'
print('Publication: main, identical export skipped, existing history/README preserved, obsolete assets removed, source index untouched.')
