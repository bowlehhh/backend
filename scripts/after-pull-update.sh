#!/usr/bin/env bash
set -euo pipefail

APP_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$APP_DIR"

echo "[1/9] Validating environment"
if [[ ! -f ".env" ]]; then
  echo "Missing .env file on server."
  exit 1
fi

if ! command -v php >/dev/null 2>&1; then
  echo "PHP CLI is required."
  exit 1
fi

if ! command -v composer >/dev/null 2>&1; then
  echo "Composer is required."
  exit 1
fi

echo "[2/9] Turning on maintenance mode"
php artisan down --retry=60 --render="errors::503" || true

cleanup() {
  echo "[9/9] Turning off maintenance mode"
  php artisan up || true
}
trap cleanup EXIT

echo "[3/9] Installing PHP dependencies from composer.lock"
composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev

if command -v npm >/dev/null 2>&1; then
  echo "[4/9] Rebuilding frontend assets"
  npm ci
  npm run build
else
  echo "[4/9] npm not found, skipping asset build"
fi

echo "[5/9] Clearing stale caches"
php artisan optimize:clear

echo "[6/9] Ensuring public storage symlink exists"
php artisan storage:link --force

echo "[7/9] Running migrations"
php artisan migrate --force

echo "[8/9] Rebuilding production caches"
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "Update after pull finished successfully."
