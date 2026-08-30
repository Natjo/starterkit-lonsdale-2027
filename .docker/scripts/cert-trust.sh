#!/bin/bash
set -e

source "$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)/lib.sh"
load_env

cd "$PROJECT_ROOT"

if [[ "$OSTYPE" == "darwin"* ]]; then
    sudo security add-trusted-cert -d -r trustRoot -k "/Library/Keychains/System.keychain" ".docker/certs/${DOMAIN}.crt"
fi

if [[ "$OSTYPE" == "linux-gnu" ]]; then
    cp .docker/certs/* /usr/local/share/ca-certificates/
    sudo update-ca-certificates
fi

echo -e "${GREEN}The cert should now be trusted in macOS System Keychain. Trusted in Chrome and Safari. (Not Firefox since it's using its own keychain manager)${NC}"
