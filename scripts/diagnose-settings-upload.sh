#!/usr/bin/env bash

set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR"

OUT_DIR="${OUT_DIR:-$ROOT_DIR/storage/logs/diagnostics}"
mkdir -p "$OUT_DIR"

TIMESTAMP="$(date -u +%Y%m%dT%H%M%SZ)"
HOSTNAME_SAFE="$(hostname | tr -cd '[:alnum:]._-')"
REPORT_FILE="$OUT_DIR/settings-upload-diagnosis-${HOSTNAME_SAFE}-${TIMESTAMP}.log"

{
    echo "============================================================"
    echo "IEDJMH - Settings Upload Diagnosis Snapshot"
    echo "timestamp_utc: $TIMESTAMP"
    echo "host: $HOSTNAME_SAFE"
    echo "project_root: $ROOT_DIR"
    echo "============================================================"
    echo

    echo "## artisan about --only=environment"
    php artisan about --only=environment || true
    echo

    echo "## artisan env"
    php artisan env || true
    echo

    echo "## artisan diagnose:settings-upload"
    php artisan diagnose:settings-upload || true
    echo

    echo "## artisan diagnose:settings-upload --json"
    php artisan diagnose:settings-upload --json || true
    echo

    echo "## Additional tinker probes"
    php artisan tinker --execute="dump(config('filesystems.default')); dump(config('filesystems.disks.public.root')); dump(config('app.url')); dump(config('session.driver')); dump(config('session.domain')); dump(config('session.secure'));" || true
    echo

    echo "## Public disk writability probes"
    php artisan tinker --execute="dump(is_writable(storage_path('app/public'))); dump(\Illuminate\Support\Facades\Storage::disk('public')->exists('settings'));" || true
    echo

    echo "## Permissions and symlink"
    if [ -d /var/www/html ]; then
        ls -ld /var/www/html/storage /var/www/html/storage/app /var/www/html/storage/app/public /var/www/html/public/storage 2>&1 || true
    else
        ls -ld storage storage/app storage/app/public public/storage 2>&1 || true
    fi
    echo

    echo "## Last laravel.log lines"
    tail -n 120 storage/logs/laravel.log 2>&1 || true
    echo

    echo "## Last nginx error lines"
    tail -n 120 /var/log/nginx/error.log 2>&1 || true
    echo

    cat <<'EOF'
## Manual reproduction (run in separate terminals during upload test)
tail -f /var/www/html/storage/logs/laravel.log
tail -f /var/log/nginx/error.log

## Browser evidence to capture (DevTools -> Network)
- POST /livewire/upload-file
- POST /livewire/update
Record: status code, response body, and whether request stays pending > 10s.
EOF
} | tee "$REPORT_FILE"

echo
echo "Diagnosis snapshot saved to:"
echo "$REPORT_FILE"
