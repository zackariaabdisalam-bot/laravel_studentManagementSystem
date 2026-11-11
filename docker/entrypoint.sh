#!/bin/bash
set -e

echo "Starting Student Management System..."

# Generate application key if missing
if [ -z "$APP_KEY" ]; then
    php artisan key:generate --force
fi

# Clear old configuration
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Run database migrations
echo "Running database migrations..."
php artisan migrate --force --no-interaction

# Create storage link
php artisan storage:link || true

# Clear and rebuild caches
php artisan cache:clear || true
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Set permissions
chown -R www-data:www-data storage bootstrap/cache

echo "Laravel setup complete."

# Start Supervisor
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
