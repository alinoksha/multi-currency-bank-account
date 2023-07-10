FROM php:8.1-fpm

RUN apt update && \
    apt install -y zip unzip git curl

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
