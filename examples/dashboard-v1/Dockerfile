# syntax=docker/dockerfile:1

# --- Stage 1: PHP/Composer dependencies ---
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
# --ignore-platform-reqs: this build stage's PHP lacks ext-intl/gd/etc;
# the runtime image (stage 3) installs them, so platform checks are deferred.
RUN composer install \
    --no-dev --no-scripts --no-interaction \
    --prefer-dist --optimize-autoloader --no-progress \
    --ignore-platform-reqs

# --- Stage 2: Frontend assets (Vite) ---
FROM node:22-alpine AS assets
WORKDIR /app
# Vite bakes VITE_* into the bundle at build time (see resources/js/echo.js).
# Pass these via Coolify build args so the Reverb websocket client has real values.
ARG VITE_APP_NAME="Analytics Dashboard"
ARG VITE_REVERB_APP_KEY
ARG VITE_REVERB_HOST
ARG VITE_REVERB_PORT=443
ARG VITE_REVERB_SCHEME=https
ENV VITE_APP_NAME=${VITE_APP_NAME} \
    VITE_REVERB_APP_KEY=${VITE_REVERB_APP_KEY} \
    VITE_REVERB_HOST=${VITE_REVERB_HOST} \
    VITE_REVERB_PORT=${VITE_REVERB_PORT} \
    VITE_REVERB_SCHEME=${VITE_REVERB_SCHEME}
COPY package.json package-lock.json ./
RUN npm ci
COPY . .
# vendor needed so any vite plugin resolving from vendor works
COPY --from=vendor /app/vendor ./vendor
RUN npm run build

# --- Stage 3: Runtime ---
# PHP 8.4 to match the locked dependencies (some use 8.4-only syntax, e.g.
# property hooks in symfony/http-foundation). composer.json allows ^8.3.
FROM php:8.4-fpm-alpine AS runtime

# System deps + PHP extensions
RUN apk add --no-cache \
        nginx supervisor bash \
        postgresql-dev libpng-dev libzip-dev icu-dev \
        freetype-dev libjpeg-turbo-dev libwebp-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
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
