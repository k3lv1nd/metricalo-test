FROM php:8.1-fpm

RUN apt update \
    && apt install -y zlib1g-dev g++ git libicu-dev zip libzip-dev openssl libicu-dev libonig-dev libxslt1-dev acl \
    && docker-php-ext-install  pdo pdo_mysql intl opcache mbstring \
    && pecl install apcu \
    && docker-php-ext-enable apcu \
    && docker-php-ext-install zip \
    && docker-php-ext-configure zip


WORKDIR /var/www/html

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer