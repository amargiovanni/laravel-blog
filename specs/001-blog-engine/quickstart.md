# Quickstart Guide: Laravel Blog Engine

**Feature**: 001-blog-engine
**Date**: 2025-11-25

This guide provides step-by-step instructions to get the Laravel Blog Engine running locally.

---

## Prerequisites

Ensure you have the following installed:

- **PHP 8.4+** with extensions: BCMath, Ctype, JSON, Mbstring, OpenSSL, PDO, Tokenizer, XML, GD or Imagick
- **Composer 2.x**
- **Node.js 20+** with npm
- **MySQL 8.0+** / **PostgreSQL 15+** / **SQLite**
- **Git**

### Recommended Tools

- [Laravel Herd](https://herd.laravel.com/) - Local PHP development environment
- [TablePlus](https://tableplus.com/) - Database GUI
- [VS Code](https://code.visualstudio.com/) with PHP extensions

---

## Installation

### 1. Clone the Repository

```bash
git clone https://github.com/amargiovanni/laravel-blog.git
cd laravel-blog
```

### 2. Install PHP Dependencies

```bash
composer install
```

### 3. Install Node Dependencies

```bash
npm install
```

### 4. Environment Setup

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 5. Configure Database

Edit `.env` and set your database credentials:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel_blog
DB_USERNAME=root
DB_PASSWORD=
```

Or use SQLite for simplicity:

```env
DB_CONNECTION=sqlite
# Ensure database/database.sqlite exists
```

### 6. Run Migrations & Seeders

```bash
# Create database tables
php artisan migrate

# Seed with sample data
php artisan db:seed
```

### 7. Build Frontend Assets

```bash
# Development build with hot reload
npm run dev

# Or production build
npm run build
```

### 8. Start Development Server

```bash
# Option 1: Laravel's built-in server
php artisan serve

# Option 2: Using Herd (recommended)
# Site is automatically available at http://laravel-blog.test

# Option 3: Composer dev script (runs Vite + server)
composer run dev
```

---

## Default Credentials

After seeding, use these credentials to log in:

### Admin User
- **Email**: admin@example.com
- **Password**: password

### Editor User
- **Email**: editor@example.com
- **Password**: password

### Author User
- **Email**: author@example.com
- **Password**: password

---

## Access Points

| URL | Description |
|-----|-------------|
| `http://localhost:8000` | Blog homepage |
| `http://localhost:8000/admin` | Filament admin panel |
| `http://localhost:8000/login` | User login |
| `http://localhost:8000/register` | User registration |
| `http://localhost:8000/feed` | RSS feed |

If using Laravel Herd:

| URL | Description |
|-----|-------------|
| `http://laravel-blog.test` | Blog homepage |
| `http://laravel-blog.test/admin` | Admin panel |

---

## Development Workflow

### Running Tests

```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Feature/PostTest.php

# Run with filter
php artisan test --filter=PostTest

# Run with coverage
php artisan test --coverage

# Run browser tests
php artisan test --filter=Browser
```

### Code Quality

```bash
# Format code with Pint
vendor/bin/pint

# Format only changed files
vendor/bin/pint --dirty

# Run static analysis
vendor/bin/phpstan analyse

# Run Larastan (Laravel-specific)
vendor/bin/phpstan analyse --memory-limit=2G
```

### Database Operations

```bash
# Fresh migration (drops all tables)
php artisan migrate:fresh

# Fresh migration with seeders
php artisan migrate:fresh --seed

# Create new migration
php artisan make:migration create_example_table

# Create model with migration, factory, seeder
php artisan make:model Example -mfs
```

### Artisan Commands

```bash
# List all commands
php artisan list

# Create Filament resource
php artisan make:filament-resource Post

# Create Livewire component
php artisan make:livewire PostList

# Create Volt component
php artisan make:volt post-list

# Clear caches
php artisan optimize:clear
```

---

## Project Structure Overview

```
laravel-blog/
├── app/
│   ├── Filament/          # Admin panel resources & pages
│   ├── Http/              # Controllers, middleware
│   ├── Livewire/          # Livewire components
│   ├── Models/            # Eloquent models
│   └── Services/          # Business logic
├── config/                # Configuration files
├── database/
│   ├── factories/         # Model factories
│   ├── migrations/        # Database migrations
│   └── seeders/           # Database seeders
├── resources/
│   ├── css/               # Stylesheets (TailwindCSS)
│   ├── js/                # JavaScript (Alpine.js)
│   └── views/             # Blade templates & Livewire
├── routes/                # Route definitions
├── specs/                 # Feature specifications
│   └── 001-blog-engine/   # This feature's docs
├── tests/                 # Test files
│   ├── Feature/           # Feature tests
│   ├── Unit/              # Unit tests
│   └── Browser/           # Browser tests
└── vendor/                # Composer dependencies
```

---

## Common Tasks

### Creating a New Post (via Admin)

1. Go to `/admin`
2. Log in as admin
3. Click "Posts" in sidebar
4. Click "Create Post"
5. Fill in title, content, categories, tags
6. Set status to "Published" or schedule for later
7. Click "Create"

### Creating a New Post (via Artisan Tinker)

```bash
php artisan tinker
```

```php
$post = \App\Models\Post::create([
    'title' => 'My First Post',
    'slug' => 'my-first-post',
    'content' => '<p>Hello World!</p>',
    'author_id' => 1,
    'status' => 'published',
    'published_at' => now(),
]);
```

### Running Scheduled Tasks

```bash
# Run scheduler manually
php artisan schedule:run

# List scheduled tasks
php artisan schedule:list

# Run specific scheduled command
php artisan posts:publish-scheduled
```

### Clearing Caches

```bash
# Clear all caches
php artisan optimize:clear

# Or individually:
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

---

## Troubleshooting

### "Class not found" errors

```bash
composer dump-autoload
```

### "Vite manifest not found" error

```bash
npm run build
# Or keep npm run dev running during development
```

### Permission errors on storage/logs

```bash
chmod -R 775 storage bootstrap/cache
```

### Database connection errors

1. Verify `.env` database settings
2. Ensure database server is running
3. Create database if it doesn't exist

### Styles not updating

```bash
# Clear Vite cache
rm -rf node_modules/.vite
npm run build
```

---

## Next Steps

1. **Explore the Admin Panel**: Log in at `/admin` and explore the dashboard, posts, and settings
2. **Customize the Theme**: Edit files in `resources/views/themes/default/`
3. **Add New Features**: Follow the speckit workflow (`/speckit.specify`, `/speckit.plan`, `/speckit.tasks`)
4. **Write Tests**: Add tests in `tests/Feature/` before implementing new features
5. **Read the Spec**: Review `specs/001-blog-engine/spec.md` for full requirements

---

## Useful Links

- [Laravel Documentation](https://laravel.com/docs/12.x)
- [Livewire Documentation](https://livewire.laravel.com/docs)
- [Filament Documentation](https://filamentphp.com/docs)
- [TailwindCSS v4 Documentation](https://tailwindcss.com/docs)
- [Pest Documentation](https://pestphp.com/docs)
