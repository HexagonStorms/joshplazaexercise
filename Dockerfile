# Just a basic PHP webserver with Apache and PHP 8.0

FROM php:8.0-apache

RUN apt-get update && apt-get install -y \
    libxml2-dev \
    libcurl4-openssl-dev \
    && docker-php-ext-install \
    dom \
    xml

RUN a2enmod rewrite

COPY . /var/www/html/

RUN chown -R www-data:www-data /var/www/html