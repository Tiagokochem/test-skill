#!/bin/bash
set -e

# Create necessary directories with proper permissions
mkdir -p bootstrap/cache storage/framework/cache storage/framework/sessions storage/framework/views storage/logs
chmod -R 775 bootstrap/cache storage
chown -R www-data:www-data bootstrap/cache storage

# Fix MySQL permissions in background (wait for MySQL to be ready)
(
    sleep 5
    until mysqladmin ping -h mysql -u root -proot --silent 2>/dev/null; do
        sleep 1
    done
    
    # Read from .env
    if [ -f ".env" ]; then
        DB_NAME=$(grep "^DB_DATABASE=" .env | cut -d '=' -f2 | tr -d '"' | tr -d "'" | xargs)
        DB_USER=$(grep "^DB_USERNAME=" .env | cut -d '=' -f2 | tr -d '"' | tr -d "'" | xargs)
        DB_PASS=$(grep "^DB_PASSWORD=" .env | cut -d '=' -f2 | tr -d '"' | tr -d "'" | xargs)
    else
        DB_NAME="task_manager_db_test"
        DB_USER="task_user"
        DB_PASS="root"
    fi
    
    mysql -h mysql -u root -proot <<EOF 2>/dev/null || true
CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\`;
CREATE USER IF NOT EXISTS '${DB_USER}'@'%' IDENTIFIED BY '${DB_PASS}';
GRANT ALL PRIVILEGES ON \`${DB_NAME}\`.* TO '${DB_USER}'@'%';
FLUSH PRIVILEGES;
EOF
) &

# Start PHP-FPM in foreground
exec php-fpm
