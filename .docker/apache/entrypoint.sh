#!/bin/sh
set -e

envsubst '${DOMAIN}' < /usr/local/apache2/conf/extra/vhosts.conf.template > /usr/local/apache2/conf/extra/vhosts.conf

exec httpd-foreground
