# Quickstart: Menu Builder

## Setup Steps

```bash
# 1. Create migrations
php artisan make:migration create_menus_table
php artisan make:migration create_menu_items_table

# 2. Create models
php artisan make:model Menu
php artisan make:model MenuItem

# 3. Create Filament resource
php artisan make:filament-resource Menu --generate

# 4. Create Blade component
php artisan make:component Navigation

# 5. Run tests
php artisan test --filter=Menu
```

## Verification

1. Access `/admin/menus`
2. Create "Main Navigation" with location "Header"
3. Add items (pages, custom links)
4. Verify drag-drop reordering works
5. Check frontend displays menu via `<x-navigation location="header" />`
