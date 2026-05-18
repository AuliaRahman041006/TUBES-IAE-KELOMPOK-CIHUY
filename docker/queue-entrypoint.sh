#!/bin/bash
set -e

echo "============================================"
echo "  Starting Queue Worker..."
echo "============================================"

# Load .env file so we can read DB credentials
if [ -f /var/www/html/.env ]; then
    export $(grep -v '^#' /var/www/html/.env | grep -E '^DB_|^REDIS_|^QUEUE_|^CACHE_' | xargs)
fi

# Wait for MySQL to be ready
echo "⏳ Queue Worker waiting for database..."
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

# Wait for Redis and app to be initialized
echo "⏳ Waiting for app initialization..."
sleep 10

# Clear caches
php artisan config:clear 2>/dev/null || true

echo "============================================"
echo "  🔄 Starting Queue Worker (Redis)..."
echo "  tries=3, timeout=60, sleep=3"
echo "============================================"

exec php artisan queue:work redis --tries=3 --timeout=60 --sleep=3 --verbose
