#!/bin/sh
set -e

echo "==> [entrypoint] Starting IEDJMH application..."

cd /var/www/html

# Ensure storage directories exist with correct permissions
echo "==> Preparing storage directories..."
mkdir -p storage/app/public \
         storage/app/private \
         storage/framework/cache/data \
         storage/framework/sessions \
         storage/framework/views \
         storage/logs \
         bootstrap/cache

chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# Create storage symlink (idempotent)
echo "==> Creating storage link..."
php artisan storage:link --force 2>/dev/null || true

# Run database migrations
echo "==> Running migrations..."
php artisan migrate --force --no-interaction

# Cache configuration for performance
echo "==> Caching configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
php artisan filament:cache-components

echo "==> Application ready. Starting Supervisor..."

# Start Supervisor (manages Nginx + PHP-FPM + Queue)
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
