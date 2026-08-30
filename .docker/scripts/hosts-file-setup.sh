#!/bin/bash
set -e

source "$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)/lib.sh"
load_env

ETC_HOSTS=/etc/hosts
IP="127.0.0.1"
HOSTS_LINE="$IP\t${DOMAIN}"

if [ -n "$(grep ${DOMAIN} /etc/hosts)" ]; then
	echo -e "${YELLOW}${DOMAIN} existe deja dans $ETC_HOSTS${NC}"
else
	echo -e "${GREEN}Ajout de ${DOMAIN} dans $ETC_HOSTS${NC}"
	sudo -- sh -c -e "echo '$HOSTS_LINE' >> /etc/hosts"

	if [ -n "$(grep ${DOMAIN} /etc/hosts)" ]; then
		echo -e "${GREEN}${DOMAIN} ajoute avec succes${NC}"
	else
		echo -e "${RED}Echec de l'ajout de ${DOMAIN}${NC}"
		exit 1
	fi
fi
