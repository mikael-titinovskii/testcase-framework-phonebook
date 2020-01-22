FROM php:7.4-fpm-alpine
RUN apk add build-base g++ cairo-dev oniguruma-dev \
    && docker-php-ext-install mysqli pdo pdo_mysql mbstring
