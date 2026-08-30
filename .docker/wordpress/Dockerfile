FROM wordpress:6.7-php8.3-fpm

RUN apt-get update \
    && apt-get install -y --no-install-recommends default-mysql-client \
    && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-install sockets \
    && curl -fsSL "https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar" -o /usr/local/bin/wp \
    && chmod +x /usr/local/bin/wp \
    && wp --info --allow-root
