FROM php:7.3-apache

EXPOSE 80:80

# General

#RUN apt-get update
#RUN apt-get install -y vim

# APACHE

# Web Root / Document Root
ENV APACHE_DOCUMENT_ROOT /var/www/html/public

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# mod_rewrite
RUN ln -s /etc/apache2/mods-available/rewrite.load /etc/apache2/mods-enabled/
RUN ln -s /etc/apache2/mods-available/headers.load /etc/apache2/mods-enabled/

# PHP

RUN echo 'display_errors = Off' >> /usr/local/etc/php/php.ini
RUN echo 'display_startup_errors = Off' >> /usr/local/etc/php/php.ini
RUN echo 'error_log = "/var/log/php/php_error.log"' >> /usr/local/etc/php/php.ini
RUN echo 'error_reporting = E_ALL' >> /usr/local/etc/php/php.ini
RUN echo 'log_errors = On' >> /usr/local/etc/php/php.ini
RUN echo 'expose_php = Off' >> /usr/local/etc/php/php.ini

# RUN mkdir -p /var/www/html/public
# RUN echo '<?php phpinfo();' > /var/www/html/public/phpinfo.php
