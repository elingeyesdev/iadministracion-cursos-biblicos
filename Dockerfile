# ---------- Etapa 1: assets (Vite + Tailwind) ----------
FROM node:22-alpine AS assets
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY vite.config.js ./
COPY resources ./resources
COPY public ./public
RUN npm run build

# ---------- Etapa 2: dependencias PHP ----------
FROM composer:2 AS vendor
WORKDIR /app
COPY . .
RUN rm -rf vendor .env .git node_modules database/database.sqlite \
    && composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader --no-scripts

# ---------- Etapa 3: Apache + PHP 8.3 ----------
FROM php:8.3-apache

RUN apt-get update && apt-get install -y --no-install-recommends libpq-dev libzip-dev libonig-dev unzip \
    && docker-php-ext-install pdo_pgsql pdo_mysql mbstring zip bcmath opcache \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri "s!/var/www/html!${APACHE_DOCUMENT_ROOT}!g" /etc/apache2/sites-available/*.conf \
    && sed -ri "s!/var/www/!${APACHE_DOCUMENT_ROOT}!g" /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

WORKDIR /var/www/html
COPY --from=vendor /app /var/www/html
COPY --from=assets /app/public/build /var/www/html/public/build

# Confiar en el proxy HTTPS de Render (evita URLs http:// y assets bloqueados)
RUN sed -i 's/->withMiddleware(function (Middleware $middleware): void {/&\n        $middleware->trustProxies(at: "*");/' bootstrap/app.php

RUN mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

# Script de arranque creado dentro del propio Dockerfile
RUN printf '%s\n' \
  '#!/bin/sh' \
  'set -e' \
  'PORT="${PORT:-10000}"' \
  'sed -ri "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf' \
  'sed -ri "s/<VirtualHost \*:80>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf' \
  'cd /var/www/html' \
  'php artisan config:clear' \
  'php artisan package:discover --ansi' \
  'php artisan config:cache' \
  'php artisan route:cache' \
  'php artisan view:cache' \
  'php artisan migrate --force' \
  'if [ "${RUN_SEED:-0}" = "1" ]; then php artisan db:seed --force; fi' \
  'exec apache2-foreground' \
  > /usr/local/bin/start.sh && chmod +x /usr/local/bin/start.sh

EXPOSE 10000
CMD ["/usr/local/bin/start.sh"]
