#!/usr/bin/env bash
# exit on error
set -o errexit

echo "--- Running Composer ---"
composer install --no-dev --optimize-autoloader

echo "--- Caching Laravel configuration ---"
# On ne peut pas facilement migrer pendant le build si la DB n'est pas accessible,
# mais Render permet de le faire. On va préparer les caches.
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Note: Les migrations seront lancées au démarrage du service via la commande de démarrage
# ou dans un "Post Build Command" sur Render.
