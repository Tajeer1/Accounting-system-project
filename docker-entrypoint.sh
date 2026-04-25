#!/usr/bin/env bash
set -e

echo "==> Booting Event Plus (Laravel on Render)"

# Generate APP_KEY if missing (shouldn't happen if set as env var)
if [ -z "$APP_KEY" ]; then
  echo "==> Generating APP_KEY"
  php artisan key:generate --force
fi

# Cache configs for production
echo "==> Caching configs/routes/views"
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run migrations
echo "==> Running migrations"
php artisan migrate --force

# Seed on first deploy (when FRESH_DB=true)
if [ "$FRESH_DB" = "true" ]; then
  echo "==> Seeding demo data (FRESH_DB=true)"
  php artisan db:seed --force
fi

# Clear any cached items that might be stale
php artisan storage:link || true

echo "==> Starting Apache on port ${PORT:-10000}"
exec "$@"
