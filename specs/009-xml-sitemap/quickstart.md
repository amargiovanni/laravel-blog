# Quickstart: XML Sitemap

## Prerequisites

- Laravel 12 application with existing Post, Category, Tag models
- 003-static-pages feature implemented (Page model)
- 007-public-frontend feature implemented (public routes)

## Installation

```bash
# Install spatie/laravel-sitemap
composer require spatie/laravel-sitemap
```

## Quick Implementation

### 1. Create Sitemap Controller

```bash
php artisan make:controller SitemapController --no-interaction
```

```php
<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Page;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

class SitemapController extends Controller
{
    public function index()
    {
        $sitemap = Sitemap::create();

        // Homepage
        $sitemap->add(Url::create('/')
            ->setPriority(1.0)
            ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY));

        // Posts
        Post::published()->each(function (Post $post) use ($sitemap) {
            $sitemap->add(Url::create("/posts/{$post->slug}")
                ->setLastModificationDate($post->updated_at)
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                ->setPriority(0.8));
        });

        // Pages
        Page::published()->each(function (Page $page) use ($sitemap) {
            $sitemap->add(Url::create("/{$page->slug}")
                ->setLastModificationDate($page->updated_at)
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                ->setPriority(0.7));
        });

        // Categories
        Category::withCount('posts')->having('posts_count', '>', 0)
            ->each(function (Category $category) use ($sitemap) {
                $sitemap->add(Url::create("/category/{$category->slug}")
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                    ->setPriority(0.6));
            });

        // Tags
        Tag::withCount('posts')->having('posts_count', '>', 0)
            ->each(function (Tag $tag) use ($sitemap) {
                $sitemap->add(Url::create("/tag/{$tag->slug}")
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                    ->setPriority(0.5));
            });

        return $sitemap->toResponse(request());
    }
}
```

### 2. Add Route

```php
// routes/web.php
Route::get('sitemap.xml', [SitemapController::class, 'index'])
    ->name('sitemap');
```

### 3. Create Artisan Command (Optional - for caching)

```bash
php artisan make:command GenerateSitemap --no-interaction
```

```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Sitemap\Sitemap;
// ... import models

class GenerateSitemap extends Command
{
    protected $signature = 'sitemap:generate';
    protected $description = 'Generate the XML sitemap';

    public function handle()
    {
        $sitemap = Sitemap::create();

        // Add URLs (same logic as controller)

        $sitemap->writeToFile(public_path('sitemap.xml'));

        $this->info('Sitemap generated successfully!');
    }
}
```

### 4. Update robots.txt

```
User-agent: *
Allow: /

Sitemap: https://yourdomain.com/sitemap.xml
```

## Verification

```bash
# Access sitemap
curl http://localhost/sitemap.xml

# Validate XML structure
curl http://localhost/sitemap.xml | xmllint --format -
```

## Common Patterns

### Adding Image Sitemaps

```php
$url = Url::create("/posts/{$post->slug}")
    ->setLastModificationDate($post->updated_at);

if ($post->featured_image) {
    $url->addImage(asset($post->featured_image), $post->title);
}

$sitemap->add($url);
```

### Sitemap Index for Large Sites

```php
use Spatie\Sitemap\SitemapIndex;

$index = SitemapIndex::create()
    ->add('/sitemap_posts.xml')
    ->add('/sitemap_pages.xml')
    ->add('/sitemap_categories.xml');

$index->writeToFile(public_path('sitemap.xml'));
```

## Next Steps

1. Implement full controller with all content types
2. Add artisan command for static generation
3. Set up model observers for automatic regeneration
4. Add robots.txt sitemap reference
5. Write feature tests
