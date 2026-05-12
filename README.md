# TASKZ Laravel Application

TASKZ is now a real Laravel project. It uses Laravel routes, controllers, Eloquent models, migrations, Blade views, sessions, and MySQL/MariaDB from XAMPP.

## What I Installed

- Composer: `C:\ProgramData\ComposerSetup\bin\composer.bat`
- Laravel installer: `C:\Users\Administrator\AppData\Roaming\Composer\vendor\bin\laravel.bat`
- XAMPP PHP was added to the machine PATH: `C:\xampp\php`
- PHP `zip` extension was enabled in `C:\xampp\php\php.ini`
- PHP/Composer CA certificates were repaired so Composer can download packages

Open a new terminal after this so Windows refreshes PATH.

## Start XAMPP

Open XAMPP Control Panel and start:

- Apache
- MySQL

Then open the app in your browser:

```text
http://localhost/TASKZ/public
```

You can also use Laravel's built-in server:

```powershell
cd C:\xampp\htdocs\TASKZ
php artisan serve
```

Then open:

```text
http://127.0.0.1:8000
```

## Database Setup

The app is configured for the default XAMPP MySQL user:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=taskz
DB_USERNAME=root
DB_PASSWORD=
```

If you ever need to rebuild the database:

```powershell
cd C:\xampp\htdocs\TASKZ
C:\xampp\mysql\bin\mysql.exe -u root -e "DROP DATABASE IF EXISTS taskz; CREATE DATABASE taskz CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
php artisan migrate --force
```

## Test The App

In the browser:

1. Register a new account.
2. Create a workspace.
3. Create a project.
4. Create a sprint.
5. Create a milestone.
6. Create tasks.
7. Assign tasks to yourself.
8. Edit a task, move it across statuses, and add comments.
9. Activate and close a sprint.
10. Open your profile from the sidebar.
11. Use `Ctrl+K` for command search and `?` for shortcuts.
12. Link task dependencies from a task's detail dialog.

## Implemented Day 3 Scope

- Task dependencies with cycle protection
- Story points on create/edit
- Client-facing task context
- Database notifications for assignment/comment events
- Basic workspace search
- Auth/search rate limits
- Security headers middleware
- Command search dialog
- Keyboard shortcut reference
- Dark/light theme toggle
- Notification dropdown
- Milestone detail page
- Profile page with workspace/task/notification snapshot

## Useful Laravel Commands

```powershell
php artisan route:list
php artisan migrate
php artisan migrate:fresh
php artisan test
php artisan config:clear
php artisan view:clear
```

## Future Laravel Projects

After opening a new terminal, these should work globally:

```powershell
composer --version
laravel --version
laravel new my-app
```

If `php`, `composer`, or `laravel` is not recognized in an already-open terminal, close it and open a new one.

## Project Files To Read First

- `routes/web.php`
- `app/Http/Controllers`
- `app/Models`
- `database/migrations`
- `resources/views`
- `public/css/taskz.css`
