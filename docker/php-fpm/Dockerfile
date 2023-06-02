FROM php:8.1-fpm-alpine

RUN set -eux; \
    curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && apk add --no-cache --virtual .build-deps $PHPIZE_DEPS linux-headers \
    && pecl install xdebug-3.2.1 \
    && docker-php-ext-enable xdebug \
    && apk del -f .build-deps \
    && apk --no-cache add postgresql-libs postgresql-dev \
    && docker-php-ext-install pgsql pdo_pgsql \
    && apk del postgresql-dev

COPY docker/php-fpm/xdebug.ini /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

WORKDIR /var/www/html
