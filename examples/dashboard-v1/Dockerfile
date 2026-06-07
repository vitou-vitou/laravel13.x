# syntax=docker/dockerfile:1

# --- Stage 1: PHP/Composer dependencies ---
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install \
    --no-dev --no-scripts --no-interaction \
    --prefer-dist --optimize-autoloader --no-progress

# --- Stage 2: Frontend assets (Vite) ---
FROM node:22-alpine AS assets
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY . .
# vendor needed so any vite plugin resolving from vendor works
COPY --from=vendor /app/vendor ./vendor
RUN npm run build

# --- Stage 3: Runtime ---
FROM php:8.3-fpm-alpine AS runtime

# System deps + PHP extensions
RUN apk add --no-cache \
        nginx supervisor bash \
        postgresql-dev libpng-dev libzip-dev icu-dev oniguruma-dev \
    && docker-php-ext-install \
        pdo_pgsql pgsql bcmath pcntl gd zip intl \
    && apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .build-deps

WORKDIR /var/www/html

# App source
COPY . .
# Built vendor + assets from earlier stages
COPY --from=vendor /app/vendor ./vendor
COPY --from=assets /app/public/build ./public/build

# Config files
COPY docker/php.ini /usr/local/etc/php/conf.d/zz-app.ini
COPY docker/nginx.conf /etc/nginx/http.d/default.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Permissions for Laravel writable dirs
RUN chown -R www-data:www-data storage bootstrap/cache \
    && mkdir -p /run/nginx

EXPOSE 80 8080

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
