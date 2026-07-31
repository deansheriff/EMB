# syntax=docker/dockerfile:1.7

FROM node:20-alpine AS frontend
WORKDIR /app
COPY package.json package-lock.json tailwind.config.js ./
RUN npm ci
COPY app ./app
COPY views ./views
COPY public/assets/css ./public/assets/css
RUN npm run css:build

FROM composer:2 AS dependencies
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --prefer-dist \
    --optimize-autoloader \
    --classmap-authoritative \
    --ignore-platform-reqs

FROM php:8.2-apache-bookworm AS runtime

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        curl \
        default-mysql-client \
        libcurl4-openssl-dev \
        libjpeg62-turbo-dev \
        libonig-dev \
        libpng-dev \
        libwebp-dev \
    && docker-php-ext-configure gd --with-jpeg --with-webp \
    && docker-php-ext-install -j"$(nproc)" curl gd mbstring pdo_mysql \
    && a2enmod headers rewrite \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

COPY --chown=www-data:www-data . .
COPY --from=dependencies --chown=www-data:www-data /app/vendor ./vendor
COPY --from=frontend --chown=www-data:www-data /app/public/assets/css/app.css ./public/assets/css/app.css
COPY docker/apache-vhost.conf /etc/apache2/sites-available/000-default.conf
COPY docker/php-production.ini /usr/local/etc/php/conf.d/zz-emb-production.ini
COPY docker/entrypoint.sh /usr/local/bin/emb-entrypoint

RUN chmod +x /usr/local/bin/emb-entrypoint \
    && mkdir -p public/uploads storage/logs storage/grant-documents \
    && chown -R www-data:www-data public/uploads storage

ENV APP_ENV=production \
    APP_DEBUG=false \
    APP_TIMEZONE=Africa/Lagos \
    SESSION_SECURE=true \
    DB_PORT=3306 \
    DB_BOOTSTRAP=true \
    DB_WAIT_TIMEOUT=60 \
    UPLOAD_MAX_MB=8 \
    GRANT_UPLOAD_TOTAL_MB=18

EXPOSE 80

ENTRYPOINT ["emb-entrypoint"]
CMD ["apache2-foreground"]

HEALTHCHECK --interval=30s --timeout=5s --start-period=40s --retries=3 \
    CMD curl --fail --silent --show-error http://127.0.0.1/health >/dev/null || exit 1
