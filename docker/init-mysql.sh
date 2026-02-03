#!/bin/bash

# Wait for MySQL to be ready
until mysqladmin ping -h localhost -u root -proot --silent 2>/dev/null; do
  sleep 1
done

# Get database name from environment (read from .env if mounted)
if [ -f /var/www/html/.env ]; then
    DB_NAME=$(grep "^DB_DATABASE=" /var/www/html/.env | cut -d '=' -f2 | tr -d '"' | tr -d "'" | xargs)
    DB_USER=$(grep "^DB_USERNAME=" /var/www/html/.env | cut -d '=' -f2 | tr -d '"' | tr -d "'" | xargs)
    DB_PASS=$(grep "^DB_PASSWORD=" /var/www/html/.env | cut -d '=' -f2 | tr -d '"' | tr -d "'" | xargs)
else
    DB_NAME=${DB_DATABASE:-task_manager_db_test}
    DB_USER=${DB_USERNAME:-task_user}
    DB_PASS=${DB_PASSWORD:-root}
fi

# Create database
mysql -u root -proot -e "CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\`;" 2>/dev/null

# Create user and grant privileges (drop and recreate to ensure permissions)
mysql -u root -proot <<EOF 2>/dev/null
DROP USER IF EXISTS '${DB_USER}'@'%';
CREATE USER '${DB_USER}'@'%' IDENTIFIED BY '${DB_PASS}';
GRANT ALL PRIVILEGES ON \`${DB_NAME}\`.* TO '${DB_USER}'@'%';
FLUSH PRIVILEGES;
EOF

echo "MySQL initialized: Database '${DB_NAME}' and user '${DB_USER}' ready"
