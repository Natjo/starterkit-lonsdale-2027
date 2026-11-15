"""A trusted local certificate works; unrelated certificates still fail."""
import http.server
import pathlib
import ssl
import subprocess
import tempfile
import threading
import urllib.error
import urllib.request
from local_tls import local_context

class Handler(http.server.BaseHTTPRequestHandler):
    def do_GET(self):
        self.send_response(200);self.end_headers();self.wfile.write(b'Design System')
    def log_message(self, *args):
        pass

with tempfile.TemporaryDirectory(prefix='ds-tls-test-') as temporary:
    root=pathlib.Path(temporary)
    certificates=root/'.docker/certs';certificates.mkdir(parents=True)
    certificate=certificates/'localhost.crt';key=root/'localhost.key'
    subprocess.run(['openssl','req','-x509','-newkey','rsa:2048','-nodes','-days','1',
                    '-subj','/CN=localhost','-addext','subjectAltName=DNS:localhost',
                    '-keyout',str(key),'-out',str(certificate)],check=True,capture_output=True)
    server=http.server.HTTPServer(('127.0.0.1',0),Handler)
    tls=ssl.SSLContext(ssl.PROTOCOL_TLS_SERVER);tls.load_cert_chain(certificate,key)
    server.socket=tls.wrap_socket(server.socket,server_side=True)
    thread=threading.Thread(target=server.serve_forever,daemon=True);thread.start()
    try:
        address='https://localhost:'+str(server.server_port)+'/'
        context=local_context(root,'localhost')
        assert context.check_hostname and context.verify_mode==ssl.CERT_REQUIRED
        assert urllib.request.urlopen(address,context=context,timeout=5).read()==b'Design System'
        try:
            urllib.request.urlopen(address,context=ssl.create_default_context(),timeout=5)
        except urllib.error.URLError as error:
            assert isinstance(error.reason,ssl.SSLCertVerificationError)
        else:
            raise AssertionError('Unknown self-signed certificate must be rejected')
        try:
            urllib.request.urlopen(address.replace('localhost','127.0.0.1'),context=context,timeout=5)
        except urllib.error.URLError as error:
            assert isinstance(error.reason,ssl.SSLCertVerificationError)
        else:
            raise AssertionError('Wrong hostname must be rejected')
    finally:
        server.shutdown();server.server_close();thread.join()
print('Local TLS: Docker certificate trusted, unknown certificates and wrong hostnames rejected.')
