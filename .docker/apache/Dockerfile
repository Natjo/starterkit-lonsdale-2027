FROM httpd:2.4-alpine

RUN apk add --no-cache gettext

RUN sed -i \
    -e 's/^#LoadModule ssl_module/LoadModule ssl_module/' \
    -e 's/^#LoadModule socache_shmcb_module/LoadModule socache_shmcb_module/' \
    -e 's/^#LoadModule rewrite_module/LoadModule rewrite_module/' \
    -e 's/^#LoadModule proxy_module/LoadModule proxy_module/' \
    -e 's/^#LoadModule proxy_fcgi_module/LoadModule proxy_fcgi_module/' \
    -e 's/^#LoadModule headers_module/LoadModule headers_module/' \
    -e 's/^#LoadModule deflate_module/LoadModule deflate_module/' \
    -e 's/^#LoadModule filter_module/LoadModule filter_module/' \
    -e 's/^#LoadModule setenvif_module/LoadModule setenvif_module/' \
    -e '/^Include conf\/extra\/httpd-ssl.conf/d' \
    -e '/^#Include conf\/extra\/httpd-ssl.conf/d' \
    -e '/^Listen 443/d' \
    /usr/local/apache2/conf/httpd.conf

RUN echo "Listen 443" >> /usr/local/apache2/conf/httpd.conf \
    && echo "Include conf/extra/vhosts.conf" >> /usr/local/apache2/conf/httpd.conf

COPY apache/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
