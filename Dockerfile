FROM dunglas/frankenphp:1-php8.3-alpine

RUN install-php-extensions pdo_pgsql bcmath zip gd pcntl exif mbstring

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Force cache break by copying a single file first
COPY composer.json composer.lock ./

RUN COMPOSER_ALLOW_SUPERUSER=1 composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --ignore-platform-reqs --no-scripts

# Copy the rest of the application
COPY . .

# Set permissions for Laravel
RUN mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

# Copy Caddyfile (even though we aren't using it, it stops errors)
COPY Caddyfile /etc/caddy/Caddyfile

EXPOSE 8080

CMD php artisan serve --host=0.0.0.0 --port=8080
