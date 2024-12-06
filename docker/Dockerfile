FROM php:8.3-cli-alpine AS base

RUN docker-php-ext-install pdo_mysql && \
    curl -o /usr/local/bin/composer https://getcomposer.org/download/latest-stable/composer.phar && \
    chmod +x /usr/local/bin/composer

WORKDIR /app


FROM base AS dev

RUN apk update && \
    apk add php83-pecl-xdebug && \
    ln -s /usr/lib/php83/modules/xdebug.so `php -i | awk '$1=="extension_dir" {print $NF}'`/xdebug.so

COPY ./php-local.ini /usr/local/etc/php/conf.d/90-local.ini

CMD /bin/sh


FROM base AS app-kubernetes

ENV APP_ENV=prod APP_DEBUG=0 COMPOSER_ALLOW_SUPERUSER=1

COPY . /app
RUN composer install --no-cache --no-dev --optimize-autoloader && \
    composer run-script apidoc
