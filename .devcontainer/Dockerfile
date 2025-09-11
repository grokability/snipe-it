FROM php:8.3-apache

RUN apt-get update && apt-get install -y \
    libonig-dev \
    git curl zip unzip libpng-dev libjpeg-dev libfreetype6-dev libxml2-dev \
    libzip-dev libpq-dev libicu-dev g++ mariadb-client vim nano libldap2-dev libmagickwand-dev libcurl4-openssl-dev libsodium-dev --no-install-recommends

RUN docker-php-ext-configure gd --with-freetype --with-jpeg

RUN docker-php-ext-install -j$(nproc) pdo pdo_mysql mbstring curl mysqli zip fileinfo bcmath xml sodium exif gd ldap

RUN pecl install imagick && docker-php-ext-enable imagick

RUN a2enmod rewrite

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /workspace/snipe-it