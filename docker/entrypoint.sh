#!/bin/sh
set -e

echo "==> [entrypoint] Starting IEDJMH application..."

cd /var/www/html

# -------------------------------------------------------
#  1. Prepare storage directories & permissions
# -------------------------------------------------------
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

# -------------------------------------------------------
#  2. Create storage symlink (idempotent)
# -------------------------------------------------------
echo "==> Creating storage link..."
php artisan storage:link --force 2>/dev/null || true

# -------------------------------------------------------
#  3. Wait for database to be ready (max 30 seconds)
# -------------------------------------------------------
echo "==> Waiting for database connection..."
MAX_RETRIES=15
RETRY_COUNT=0
until php artisan db:monitor --databases=mysql 2>/dev/null | grep -q "OK" || [ $RETRY_COUNT -ge $MAX_RETRIES ]; do
    RETRY_COUNT=$((RETRY_COUNT + 1))
    echo "    Database not ready, retrying ($RETRY_COUNT/$MAX_RETRIES)..."
    sleep 2
done

if [ $RETRY_COUNT -ge $MAX_RETRIES ]; then
    echo "    WARNING: Database may not be ready, continuing anyway..."
fi

# -------------------------------------------------------
#  4. Run database migrations
# -------------------------------------------------------
echo "==> Running migrations..."
php artisan migrate --force --no-interaction 2>&1 || echo "    WARNING: Migrations had issues (may already be up to date)"

# -------------------------------------------------------
#  5. Cache configuration for performance
# -------------------------------------------------------
echo "==> Caching configuration..."
php artisan config:cache 2>&1 || echo "    WARNING: config:cache failed"
php artisan route:cache 2>&1 || echo "    WARNING: route:cache failed"
php artisan view:cache 2>&1 || echo "    WARNING: view:cache failed"
php artisan event:cache 2>&1 || echo "    WARNING: event:cache failed"
php artisan filament:cache-components 2>&1 || echo "    WARNING: filament:cache-components failed"

# -------------------------------------------------------
#  6. Ensure PHP-FPM socket directory exists
# -------------------------------------------------------
mkdir -p /run/php
chown www-data:www-data /run/php

# -------------------------------------------------------
#  7. Start Supervisor (Nginx + PHP-FPM + Queue Worker)
# -------------------------------------------------------
echo "==> Application ready. Starting Supervisor..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
