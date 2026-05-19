#!/usr/bin/env bash
# Chạy trên server Linux sau: git pull origin main
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

echo "==> Composer (production)"
composer install --no-dev --optimize-autoloader --no-interaction

echo "==> Laravel migrate & link storage"
php artisan migrate --force
php artisan storage:link 2>/dev/null || true

echo "==> Cache"
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "==> Done. Kiểm tra APP_URL và quyền storage/ trên host."
