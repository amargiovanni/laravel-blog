# Quickstart: Full-Text Search

```bash
composer require laravel/scout
php artisan vendor:publish --provider="Laravel\Scout\ScoutServiceProvider"
# Add Searchable trait to Post, Page models
php artisan scout:import "App\Models\Post"
```
