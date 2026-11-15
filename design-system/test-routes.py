"""Exercise the virtual route/access contract without WordPress or Docker."""
import pathlib, tempfile, shutil, subprocess, json
source=pathlib.Path(__file__).parent
with tempfile.TemporaryDirectory() as temporary:
    root=pathlib.Path(temporary)
    shutil.copyfile(source/'bootstrap.php',root/'bootstrap.php')
    (root/'design-system.css').write_text('/* public local demo */')
    def run(local, path, project=False, ticket=False):
        marker=root/'project.json'
        if project: marker.write_text('{}')
        elif marker.exists(): marker.unlink()
        script='''<?php
ob_start();
define('ABSPATH', '/unused/'); define('HOUR_IN_SECONDS',3600);
function home_url($path){return 'https://test.code'.$path;}
function add_action($name,$callback,$priority){$GLOBALS['route']=$callback;}
function status_header($code){echo 'STATUS:'.$code;}
function nocache_headers(){}
function get_transient($key){return isset($_GET['forge_ds_ticket']) && $key === 'forge_ds_ticket_'.hash('sha256',$_GET['forge_ds_ticket']) ? 'ready' : false;}
function delete_transient($key){$GLOBALS['consumed']=true;}
function set_transient($key,$value,$ttl){$GLOBALS['session']=$ttl;}
function is_ssl(){return true;}
function remove_query_arg($key){return 'https://test.code/design-system/components/';}
function wp_safe_redirect($url,$code){echo json_encode([$url,$code,$GLOBALS['consumed'],$GLOBALS['session']]);}
'''
        script+='define("ENV_LOCAL",'+('true' if local else 'false')+');\n'
        script+='$_SERVER["REQUEST_URI"]='+json.dumps(path)+';\n'
        if ticket: script+='$_GET["forge_ds_ticket"]=str_repeat("a",64);\n'
        script+='require '+json.dumps(str(root/'bootstrap.php'))+'; if(isset($GLOBALS["route"]))($GLOBALS["route"])();'
        result=subprocess.run(['php'],input=script,text=True,capture_output=True)
        assert result.returncode==0,result.stderr
        return result.stdout
    assert run(False,'/design-system/')==''
    assert run(True,'/design-system/assets/design-system.css')=='STATUS:200/* public local demo */'
    assert run(True,'/design-system/assets/bootstrap.php')=='STATUS:404'
    assert run(True,'/design-system/components/',True)=='STATUS:404'
    assert run(True,'/design-system/assets/design-system.css',True)=='STATUS:404'
    value=json.loads(run(True,'/design-system/components/?forge_ds_ticket='+('a'*64),True,True))
    assert value==['https://test.code/design-system/components/',303,True,3600],value
print('Virtual routes: production disabled, local assets served, PHP paths denied, project requires session, one-time ticket consumed and removed from URL.')
