FROM php:7.3-apache

#EXPOSE 80

# General

WORKDIR /var/www/html

# Apache

ENV APACHE_DOCUMENT_ROOT /var/www/html/public

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
 && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

RUN ln -s /etc/apache2/mods-available/rewrite.load /etc/apache2/mods-enabled/ \
 && ln -s /etc/apache2/mods-available/headers.load /etc/apache2/mods-enabled/

# PHP

# PHP_INI_DIR = /usr/local/etc/php

RUN apt-get -y update \
 && apt-get install -y \
   libicu-dev \
   libjpeg-dev \
   libpng-dev \
   libfreetype6-dev \
 && docker-php-ext-configure intl \
 && docker-php-ext-install intl \
 && docker-php-ext-configure gd \
   --with-jpeg-dir \
   --with-png-dir \
   --with-freetype-dir \
 && docker-php-ext-install gd

COPY config/environment/php.ini $PHP_INI_DIR/conf.d

# App

#COPY . .

# Debug

#RUN echo '<?php phpinfo();' > $APACHE_DOCUMENT_ROOT/phpinfo.php
