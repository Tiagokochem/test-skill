#!/bin/bash
# This script runs every time MySQL starts to ensure permissions are correct

# Wait for MySQL
sleep 2

# Read from environment variables (set by docker-compose)
DB_NAME=${DB_DATABASE:-task_manager_db_test}
DB_USER=${DB_USERNAME:-task_user}
DB_PASS=${DB_PASSWORD:-root}

# Fix permissions
mysql -u root -proot <<EOF 2>/dev/null
CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\`;
CREATE USER IF NOT EXISTS '${DB_USER}'@'%' IDENTIFIED BY '${DB_PASS}';
GRANT ALL PRIVILEGES ON \`${DB_NAME}\`.* TO '${DB_USER}'@'%';
FLUSH PRIVILEGES;
EOF
