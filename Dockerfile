FROM flyimg/docker-app

COPY .    /var/www/html

#add www-data + mdkdir var folder
RUN usermod -u 1000 www-data && \
    mkdir -p /var/www/html/var web/uploads/.tmb var/cache/ var/log/ && \
    chown -R www-data:www-data var/  web/uploads/ && \
    chmod 777 -R var/  web/uploads/

RUN sed -i -e "s/server\s{/server {\n    client_max_body_size 16M;/g" /etc/nginx/conf.d/flyimage.conf && \
    echo -e "upload_max_filesize=16M\npost_max_size=16M\nmemory_limit = 64M" > /usr/local/etc/php/conf.d/php.ini

EXPOSE 80

CMD /usr/bin/supervisord
