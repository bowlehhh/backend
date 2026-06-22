#!/usr/bin/env bash
set -euo pipefail

APP_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$APP_DIR"

echo "[1/8] Checking required files"
if [[ ! -f ".env" ]]; then
  echo "Missing .env file. Copy .env.example to .env and set production values first."
  exit 1
fi

if ! command -v php >/dev/null 2>&1; then
  echo "PHP CLI is required on the server."
  exit 1
fi

if ! command -v composer >/dev/null 2>&1; then
  echo "Composer is required on the server."
  exit 1
fi

echo "[2/8] Enabling maintenance mode"
php artisan down --retry=60 --render="errors::503" || true

cleanup() {
  echo "[8/8] Disabling maintenance mode"
  php artisan up || true
}
trap cleanup EXIT

echo "[3/8] Installing PHP dependencies"
composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev

if command -v npm >/dev/null 2>&1; then
  echo "[4/8] Installing and building frontend assets"
  npm ci
  npm run build
else
  echo "[4/8] Skipping frontend build because npm is not installed"
fi

echo "[5/8] Preparing Laravel caches and symlinks"
php artisan optimize:clear
php artisan storage:link --force

echo "[6/8] Running database migrations"
php artisan migrate --force

echo "[7/8] Caching configuration"
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "Deployment finished successfully."
