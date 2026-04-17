#!/bin/sh
set -e

echo "Optimizing application for production..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "Running migrations..."
php artisan migrate --force
php artisan db:seed --class=RolesAndPermissionsSeeder

echo "Starting Supervisor..."
exec /usr/bin/supervisord -c /etc/supervisord.conf
