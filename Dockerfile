FROM php:7.3-apache

EXPOSE 80

# General

WORKDIR /var/www/html

# Apache

ENV APACHE_DOCUMENT_ROOT /var/www/html/public

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
 && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

RUN ln -s /etc/apache2/mods-available/rewrite.load /etc/apache2/mods-enabled/ \
 && ln -s /etc/apache2/mods-available/headers.load /etc/apache2/mods-enabled/

# PHP

RUN echo 'display_errors = Off' >> /usr/local/etc/php/php.ini \
 && echo 'display_startup_errors = Off' >> /usr/local/etc/php/php.ini \
 && echo 'error_log = "/var/log/php/php_error.log"' >> /usr/local/etc/php/php.ini \
 && echo 'error_reporting = E_ALL' >> /usr/local/etc/php/php.ini \
 && echo 'log_errors = On' >> /usr/local/etc/php/php.ini \
 && echo 'expose_php = Off' >> /usr/local/etc/php/php.ini

# App

COPY . .

# Debug

RUN echo '<?php phpinfo();' > /var/www/html/public/phpinfo.php
