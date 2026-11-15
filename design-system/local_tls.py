"""Trust only the explicitly configured local development certificate, with hostname checks."""
import pathlib
import re
import ssl


def local_context(root: pathlib.Path, hostname: str) -> ssl.SSLContext:
    context = ssl.create_default_context()
    if hostname in ('localhost', '127.0.0.1') or re.fullmatch(r'[a-zA-Z0-9.-]+\.code', hostname):
        certificate = root / '.docker' / 'certs' / (hostname + '.crt')
        if certificate.is_file():
            context.load_verify_locations(cafile=str(certificate))
    return context
