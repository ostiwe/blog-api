FROM php:7.4-fpm-buster

RUN    pecl install redis-5.3.0   \
    && pecl install xdebug-2.9.6 \
    && docker-php-ext-enable redis xdebug \
    && docker-php-ext-install pdo pdo_mysql

EXPOSE 9000
