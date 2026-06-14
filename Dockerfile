FROM php:8.3-fpm-alpine

# System dependencies
RUN apk add --no-cache git curl unzip nodejs npm sqlite

# PHP extensions
RUN docker-php-ext-install pdo pdo_sqlite opcache \
    && pecl install redis \
    && docker-php-ext-enable redis

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Install PHP dependencies
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-scripts --no-interaction

# Install and build JS
COPY package.json package-lock.json ./
RUN npm ci --prefer-offline

# Copy application
COPY . .

# Build assets
RUN npm run build

# Permissions and optimisation
RUN chown -R www-data:www-data storage bootstrap/cache \
    && php artisan config:clear \
    && php artisan route:cache \
    && php artisan view:cache

USER www-data

EXPOSE 9000
