#!/bin/bash
set -e

source "$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)/lib.sh"
load_env

cd "$PROJECT_ROOT"

OPENSSL_CNF_PATH=""
if [[ "$OSTYPE" == "linux-gnu" ]]; then
    OPENSSL_CNF_PATH=/etc/ssl/openssl.cnf
elif [[ "$OSTYPE" == "darwin"* ]]; then
    for candidate in \
        /opt/homebrew/etc/openssl@3/openssl.cnf \
        /usr/local/etc/openssl@3/openssl.cnf \
        /opt/homebrew/etc/openssl/openssl.cnf \
        /usr/local/etc/openssl/openssl.cnf \
        /System/Library/OpenSSL/openssl.cnf; do
        if [ -f "$candidate" ]; then
            OPENSSL_CNF_PATH="$candidate"
            break
        fi
    done
    if [ -z "$OPENSSL_CNF_PATH" ]; then
        echo -e "${RED}Erreur: openssl.cnf introuvable. Installez openssl via Homebrew: brew install openssl${NC}"
        exit 1
    fi
fi

openssl req \
    -newkey rsa:2048 \
    -x509 \
    -nodes \
    -keyout "${DOMAIN}".key \
    -new \
    -out "${DOMAIN}".crt \
    -subj /CN="${DOMAIN}" \
    -reqexts SAN \
    -extensions SAN \
    -config <(cat $OPENSSL_CNF_PATH \
        <(printf '[SAN]\nsubjectAltName=DNS:'${DOMAIN})) \
    -sha256 \
    -days 365

rm -rf .docker/certs/*.crt .docker/certs/*.key

mv "${DOMAIN}".crt .docker/certs/
mv "${DOMAIN}".key .docker/certs/

echo -e "${GREEN}Cert created in .docker/certs/${NC}"
