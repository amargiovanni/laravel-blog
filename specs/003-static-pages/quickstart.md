# Quickstart: Static Pages

## Prerequisites

- Laravel 12 application running
- Filament 4.x installed and configured
- Database migrations up to date
- Node.js for frontend assets

## Setup Steps

### 1. Create Migration

```bash
php artisan make:migration create_pages_table
```

### 2. Create Model

```bash
php artisan make:model Page
```

### 3. Create Filament Resource

```bash
php artisan make:filament-resource Page --generate
```

### 4. Create Policy

```bash
php artisan make:policy PagePolicy --model=Page
```

### 5. Create Frontend Controller

```bash
php artisan make:controller PageController
```

### 6. Create Blade Templates

Create the following files in `resources/views/pages/`:
- `show.blade.php` (default template)
- `full-width.blade.php`
- `with-sidebar.blade.php`

### 7. Register Routes

Add to `routes/web.php` (at the end, as catch-all):

```php
Route::get('/{slug}', [PageController::class, 'show'])
    ->where('slug', '.*')
    ->name('pages.show');
```

### 8. Run Tests

```bash
php artisan test --filter=Page
```

## Verification

1. Access `/admin/pages` to verify Filament resource
2. Create a test page with slug "test"
3. Access `/test` to verify frontend display
4. Create a nested page under "test"
5. Access `/test/child` to verify nested routing

## Common Issues

- **404 on page access**: Check route order in web.php
- **Slug conflicts**: Check validation rules
- **Permission denied**: Verify PagePolicy and Shield permissions
