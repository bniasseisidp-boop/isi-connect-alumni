#!/bin/bash
set -e

echo "--- Caching configurations ---"
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "--- Running Migrations & Seeding ---"
# Force database migration on startup
php artisan migrate --force
# Force seed the admin
php artisan db:seed --class=UserSeeder --force

echo "--- Starting Apache ---"
# Start Apache in foreground
apache2-foreground
