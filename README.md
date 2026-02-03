# Task Manager - Laravel Application

Simple web application for task management developed with Laravel 11 and PHP 8.3.

## Features

- ✅ Create tasks (name, priority, timestamps)
- ✅ Edit tasks
- ✅ Delete tasks
- ✅ Reorder tasks with drag and drop (priority automatically updated)
- ✅ Manage projects
- ✅ Filter tasks by project

## Requirements

- Docker
- Docker Compose

## Installation and Setup

### 1. Create the `.env` file

Create a `.env` file in the project root with the following content:

```env
APP_NAME="Task Manager"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_TIMEZONE=UTC
APP_URL=http://localhost:8081

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=task_manager_db_test
DB_USERNAME=task_user
DB_PASSWORD=root

SESSION_DRIVER=database
SESSION_LIFETIME=120

CACHE_STORE=database
QUEUE_CONNECTION=database
```

**Important:** If you change the database name in `.env`, you need to recreate the MySQL volume:

```bash
# Stop containers
docker compose down

# Remove the MySQL volume (this will delete all data!)
docker volume rm newtest_mysql_data

# Start containers again (will create new database)
docker compose up -d
```

### 2. Build and start Docker containers

```bash
docker compose build
docker compose up -d
```

### 3. Install Composer dependencies

```bash
docker compose exec app composer install
```

### 4. Generate application key

```bash
docker compose exec app php artisan key:generate
```

### 5. Run migrations

```bash
docker compose exec app php artisan migrate
```

### 6. Access the application

Open your browser and navigate to:

```
http://localhost:8081
```

## How Database Configuration Works

The application reads database settings from the `.env` file:

- **Laravel** reads: `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` from `.env`
- **Docker Compose** reads: Uses `${DB_DATABASE}` to create the MySQL database

**Important Notes:**

1. The MySQL container creates the database **only on first startup**
2. If you change `DB_DATABASE` in `.env`, you must:
   - Stop containers: `docker compose down`
   - Remove volume: `docker volume rm newtest_mysql_data`
   - Start again: `docker compose up -d`

3. Or create the database manually:
   ```bash
   docker compose exec mysql mysql -u root -proot -e "CREATE DATABASE IF NOT EXISTS task_manager_db_test;"
   ```

## Project Structure

```
newtest/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       ├── TaskController.php
│   │       └── ProjectController.php
│   └── Models/
│       ├── Task.php
│       └── Project.php
├── database/
│   └── migrations/
│       ├── 2024_01_01_000001_create_projects_table.php
│       └── 2024_01_01_000002_create_tasks_table.php
├── resources/
│   └── views/
│       ├── layouts/
│       │   └── app.blade.php
│       └── tasks/
│           └── index.blade.php
├── routes/
│   └── web.php
├── docker-compose.yml
├── Dockerfile
└── README.md
```

## How to Use

### Create a Task

1. Click the "New Task" button
2. Fill in the task name
3. (Optional) Select a project
4. Click "Save"

### Edit a Task

1. Click the pencil icon next to the task
2. Modify the name or project
3. Click "Save"

### Delete a Task

1. Click the trash icon next to the task
2. Confirm the deletion

### Reorder Tasks

1. Click and drag a task using the grip icon (⋮⋮)
2. Drop the task in the new position
3. Priority will be automatically updated

### Create a Project

1. Click the "New Project" button
2. Enter the project name
3. Click "Create"

### Filter Tasks by Project

1. Use the "All Projects" dropdown at the top
2. Select the desired project
3. Only tasks from that project will be displayed

## Useful Commands

### Access the application container

```bash
docker compose exec app bash
```

### Run Artisan commands

```bash
docker compose exec app php artisan <command>
```

### View logs

```bash
docker compose logs -f app
```

### Stop containers

```bash
docker compose down
```

### Stop and remove volumes (clean database)

```bash
docker compose down -v
```

## Technologies Used

- **Laravel 11**: PHP Framework
- **PHP 8.3**: Programming language
- **MySQL 8.0**: Database
- **Nginx**: Web server
- **Bootstrap 5**: CSS Framework
- **SortableJS**: JavaScript library for drag and drop

## Database Structure

### `projects` Table

- `id`: Unique project ID
- `name`: Project name
- `created_at`: Creation date
- `updated_at`: Update date

### `tasks` Table

- `id`: Unique task ID
- `name`: Task name
- `priority`: Task priority (1 = highest)
- `project_id`: Project ID (nullable)
- `created_at`: Creation date
- `updated_at`: Update date

## Troubleshooting

#### Error: "bootstrap/cache directory must be present and writable"

This error occurs when required directories don't exist. Run:

```bash
# Create directories and set permissions
docker compose exec app mkdir -p bootstrap/cache storage/framework/cache storage/framework/sessions storage/framework/views storage/logs
docker compose exec app chmod -R 775 bootstrap/cache storage
docker compose exec app chown -R www-data:www-data bootstrap/cache storage
```

#### Error: "Failed to open stream: No such file or directory" for vendor/autoload.php

This error occurs when Composer dependencies haven't been installed yet. Run:

```bash
docker compose exec app composer install
```

If the container isn't running, start it first:

```bash
docker compose up -d
```

#### Error: "file_get_contents(/var/www/html/.env): Failed to open stream"

This error occurs when the `.env` file doesn't exist. Create it with the content shown in step 1 of the installation.

#### Error: "SQLSTATE[HY000] [2002] Connection refused"

This means MySQL isn't ready yet. Wait a bit and try again, or check if MySQL container is running:

```bash
docker compose ps
docker compose logs mysql
```

#### Error: "Unknown database 'task_manager_db_test'"

This happens when you changed `DB_DATABASE` in `.env` but the MySQL volume still has the old database. Solutions:

1. **Recreate the volume** (deletes all data):
   ```bash
   docker compose down
   docker volume rm newtest_mysql_data
   docker compose up -d
   ```

2. **Create the database manually**:
   ```bash
   docker compose exec mysql mysql -u root -proot -e "CREATE DATABASE IF NOT EXISTS task_manager_db_test;"
   ```

#### Permission errors

If you encounter permission errors, run:

```bash
docker compose exec app chown -R www-data:www-data /var/www/html
docker compose exec app chmod -R 775 bootstrap/cache storage
```

## Development

### Run tests (when implemented)

```bash
docker compose exec app php artisan test
```

### Clear cache

```bash
docker compose exec app php artisan cache:clear
docker compose exec app php artisan config:clear
docker compose exec app php artisan route:clear
docker compose exec app php artisan view:clear
```

## License

This project is open source and available under the MIT license.
