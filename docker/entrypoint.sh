#!/bin/sh
set -e

echo "==> [entrypoint] Starting IEDJMH application..."

cd /var/www/html

# -------------------------------------------------------
#  0. Validate required environment variables
# -------------------------------------------------------
echo "==> Validating required environment variables..."

MISSING_ENV_VARS=""
for REQUIRED_VAR in APP_KEY APP_URL DB_CONNECTION DB_HOST DB_PORT DB_DATABASE DB_USERNAME; do
    eval "REQUIRED_VALUE=\${$REQUIRED_VAR:-}"

    if [ -z "$REQUIRED_VALUE" ]; then
        MISSING_ENV_VARS="$MISSING_ENV_VARS $REQUIRED_VAR"
    fi
done

if [ -n "$MISSING_ENV_VARS" ]; then
    echo "    ERROR: Missing required env vars:$MISSING_ENV_VARS"
    echo "    Configure them in Dokploy and redeploy."
    exit 1
fi

if [ "$APP_KEY" = "base64:" ] || [ "$APP_KEY" = "null" ]; then
    echo "    ERROR: APP_KEY is invalid. Define a real app key in Dokploy."
    exit 1
fi

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
    echo "    ERROR: Database is not reachable after $MAX_RETRIES retries."
    exit 1
fi

# -------------------------------------------------------
#  4. Run database migrations
# -------------------------------------------------------
echo "==> Running migrations..."
php artisan migrate --force --no-interaction

# -------------------------------------------------------
#  5. Cache configuration for performance
# -------------------------------------------------------
echo "==> Caching configuration..."
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
php artisan filament:cache-components

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
