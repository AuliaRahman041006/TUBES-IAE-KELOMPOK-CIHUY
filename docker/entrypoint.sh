#!/bin/bash
set -e

echo "============================================"
echo "  Starting Laravel Service..."
echo "============================================"

# Load .env file so we can read DB credentials
if [ -f /var/www/html/.env ]; then
    export $(grep -v '^#' /var/www/html/.env | grep -E '^DB_|^REDIS_|^QUEUE_|^CACHE_' | xargs)
fi

echo "   DB_HOST=$DB_HOST DB_DATABASE=$DB_DATABASE"

# Wait for MySQL to be ready using a simple PHP PDO connection test
echo "⏳ Waiting for database connection..."
max_tries=30
counter=0
until php -r "
try {
    \$host = '${DB_HOST}';
    \$port = '${DB_PORT}';
    \$db   = '${DB_DATABASE}';
    \$user = '${DB_USERNAME}';
    \$pass = '${DB_PASSWORD}';
    new PDO(\"mysql:host=\$host;port=\$port;dbname=\$db\", \$user, \$pass);
    exit(0);
} catch (Exception \$e) {
    exit(1);
}
" 2>/dev/null; do
    counter=$((counter + 1))
    if [ $counter -gt $max_tries ]; then
        echo "❌ Database not available after $max_tries attempts. Exiting."
        exit 1
    fi
    echo "   Attempt $counter/$max_tries - waiting 3s..."
    sleep 3
done
echo "✅ Database is ready!"

# Generate app key if not set
php artisan key:generate --force --no-interaction 2>/dev/null || true

# Run migrations
echo "🔄 Running migrations..."
php artisan migrate --force --no-interaction

# Run seeders (will fail silently if already seeded)
echo "🌱 Running seeders..."
php artisan db:seed --force --no-interaction 2>/dev/null || true

# Clear caches
echo "🧹 Clearing caches..."
php artisan config:clear
php artisan route:clear
php artisan cache:clear 2>/dev/null || true

echo "============================================"
echo "  🚀 Starting PHP-FPM..."
echo "============================================"
exec php-fpm
