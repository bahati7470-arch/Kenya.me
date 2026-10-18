FROM dunglas/frankenphp:1-php8.3-alpine

RUN install-php-extensions pdo_pgsql bcmath zip gd pcntl exif mbstring

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY . .

# We set COMPOSER_ALLOW_SUPERUSER to prevent warnings, and --no-scripts to prevent Laravel from booting during the build
RUN COMPOSER_ALLOW_SUPERUSER=1 composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --ignore-platform-reqs --no-scripts \
    && mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

COPY Caddyfile /etc/caddy/Caddyfile

EXPOSE 8080

CMD ["frankenphp", "run", "--config", "/etc/caddy/Caddyfile"]
