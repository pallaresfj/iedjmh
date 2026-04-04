# ==============================================================================
#  STAGE 1 — Install Composer dependencies
# ==============================================================================
FROM php:8.4-cli-alpine AS composer-deps

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /build

COPY composer.json composer.lock ./

# Use --ignore-platform-reqs because extensions (gd, intl, zip, bcmath)
# are only needed at runtime (Stage 3), not for downloading source files.
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-scripts \
    --no-autoloader \
    --prefer-dist \
    --ignore-platform-reqs \
    && rm -rf /root/.composer/cache


# ==============================================================================
#  STAGE 2 — Build frontend assets (Node.js + Vendor sources)
# ==============================================================================
FROM node:22-alpine AS assets

WORKDIR /build

# Install Node dependencies
COPY package.json package-lock.json ./
RUN npm ci --ignore-scripts

# Copy sources needed by Tailwind CSS @source / @import directives
COPY --from=composer-deps /build/vendor/ ./vendor/
COPY vite.config.js ./
COPY resources/ ./resources/
COPY app/ ./app/

RUN npm run build


# ==============================================================================
#  STAGE 3 — Production PHP application
# ==============================================================================
FROM php:8.4-fpm AS production

ARG APP_VERSION=latest

LABEL maintainer="AS&Servicios <info@asyservicios.com>"
LABEL app="iedjmh"
LABEL version="${APP_VERSION}"

# -----------------------------------------------------------
#  System dependencies + PHP extensions
# -----------------------------------------------------------
RUN apt-get update && apt-get install -y --no-install-recommends \
        nginx \
        supervisor \
        libpng-dev \
        libjpeg62-turbo-dev \
        libfreetype6-dev \
        libwebp-dev \
        libzip-dev \
        libicu-dev \
        libxml2-dev \
        libonig-dev \
        unzip \
        curl \
        ca-certificates \
    && docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg \
        --with-webp \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        gd \
        zip \
        intl \
        bcmath \
        pcntl \
        opcache \
        mbstring \
        xml \
        exif \
    && apt-get purge -y --auto-remove -o APT::AutoRemove::RecommendsImportant=false \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# -----------------------------------------------------------
#  PHP production configuration
# -----------------------------------------------------------
RUN cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

RUN { \
    echo "[PHP]"; \
    echo "upload_max_filesize = 64M"; \
    echo "post_max_size = 64M"; \
    echo "memory_limit = 256M"; \
    echo "max_execution_time = 300"; \
    echo "max_input_time = 300"; \
    echo "max_input_vars = 5000"; \
    echo "expose_php = Off"; \
    echo "date.timezone = America/Bogota"; \
    } > "$PHP_INI_DIR/conf.d/99-iedjmh.ini"

RUN { \
    echo "[opcache]"; \
    echo "opcache.enable=1"; \
    echo "opcache.memory_consumption=256"; \
    echo "opcache.interned_strings_buffer=16"; \
    echo "opcache.max_accelerated_files=20000"; \
    echo "opcache.validate_timestamps=0"; \
    echo "opcache.save_comments=1"; \
    echo "opcache.jit=0"; \
    echo "opcache.jit_buffer_size=0"; \
    } > "$PHP_INI_DIR/conf.d/opcache.ini"

# PHP-FPM: keep default TCP listener on port 9000
RUN mkdir -p /run/php

# -----------------------------------------------------------
#  Composer binary
# -----------------------------------------------------------
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# -----------------------------------------------------------
#  Nginx & Supervisor configuration
# -----------------------------------------------------------
RUN rm -f /etc/nginx/sites-enabled/default /etc/nginx/sites-available/default \
         /etc/nginx/conf.d/default.conf
COPY docker/nginx.conf /etc/nginx/conf.d/iedjmh.conf

COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# -----------------------------------------------------------
#  Application code
# -----------------------------------------------------------
WORKDIR /var/www/html

# Copy vendor from Stage 1 (already installed)
COPY --from=composer-deps /build/vendor/ ./vendor/

# Copy application source
COPY . .

# Generate optimized autoloader + run post-install scripts
RUN composer dump-autoload --optimize --no-dev \
    && (php artisan package:discover --ansi || true) \
    && (php artisan filament:upgrade || true)

# Copy compiled assets from Stage 2
COPY --from=assets /build/public/build/ ./public/build/

# -----------------------------------------------------------
#  Permissions & directories
# -----------------------------------------------------------
RUN mkdir -p \
        storage/app/public \
        storage/app/private \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
        bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

RUN mkdir -p /var/log/supervisor /var/log/nginx \
    && touch /var/log/nginx/access.log /var/log/nginx/error.log

# -----------------------------------------------------------
#  Entrypoint & Health check
# -----------------------------------------------------------
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 80

HEALTHCHECK --interval=30s --timeout=5s --retries=3 --start-period=15s \
    CMD curl -f http://localhost/up || exit 1

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
