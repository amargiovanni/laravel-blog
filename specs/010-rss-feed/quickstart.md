# Quickstart: RSS Feed

## Prerequisites

- Laravel 12 application with existing Post, Category, User models
- Public frontend routes from 007-public-frontend

## Installation

```bash
# Install spatie/laravel-feed
composer require spatie/laravel-feed

# Publish configuration
php artisan vendor:publish --tag=feed-config
```

## Quick Implementation

### 1. Configure Main Feed (config/feed.php)

```php
<?php

return [
    'feeds' => [
        'main' => [
            'items' => ['App\Models\Post', 'getFeedItems'],
            'url' => '/feed',
            'title' => config('app.name') . ' RSS Feed',
            'description' => 'Latest posts from our blog',
            'language' => 'en-US',
            'format' => 'rss',
            'view' => 'feed::rss',
        ],
    ],
];
```

### 2. Add Feedable Interface to Post Model

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Spatie\Feed\Feedable;
use Spatie\Feed\FeedItem;

class Post extends Model implements Feedable
{
    public function toFeedItem(): FeedItem
    {
        return FeedItem::create()
            ->id($this->id)
            ->title($this->title)
            ->summary($this->excerpt ?? Str::limit(strip_tags($this->content), 500))
            ->updated($this->updated_at)
            ->link(route('posts.show', $this->slug))
            ->authorName($this->author->name);
    }

    public static function getFeedItems()
    {
        return static::query()
            ->published()
            ->with(['author', 'category'])
            ->latest('published_at')
            ->take(20)
            ->get();
    }
}
```

### 3. Register Feed Routes

The package auto-registers routes based on config. Verify in routes:

```bash
php artisan route:list --name=feed
```

### 4. Add Feed Auto-Discovery to Layout

In your main layout file (e.g., `resources/views/components/layouts/app.blade.php`):

```blade
<head>
    <!-- Other head content -->

    @include('feed::links')
</head>
```

Or manually:

```blade
<link rel="alternate" type="application/rss+xml"
      title="{{ config('app.name') }} RSS Feed"
      href="{{ route('feeds.main') }}">
```

### 5. Create FeedController for Dynamic Feeds

```bash
php artisan make:controller FeedController --no-interaction
```

```php
<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Spatie\Feed\Feed;

class FeedController extends Controller
{
    public function category(string $slug)
    {
        $category = Category::where('slug', $slug)->firstOrFail();

        $posts = Post::query()
            ->published()
            ->where('category_id', $category->id)
            ->with(['author'])
            ->latest('published_at')
            ->take(20)
            ->get();

        return new Feed(
            "{$category->name} - " . config('app.name'),
            $posts,
            request()->url(),
            'feed::rss',
            "Posts in {$category->name} category"
        );
    }

    public function author(int $id)
    {
        $author = User::findOrFail($id);

        $posts = Post::query()
            ->published()
            ->where('author_id', $author->id)
            ->with(['category'])
            ->latest('published_at')
            ->take(20)
            ->get();

        return new Feed(
            "Posts by {$author->name} - " . config('app.name'),
            $posts,
            request()->url(),
            'feed::rss',
            "Posts written by {$author->name}"
        );
    }
}
```

### 6. Add Custom Feed Routes

```php
// routes/web.php

Route::get('category/{slug}/feed', [FeedController::class, 'category'])
    ->name('category.feed');

Route::get('author/{id}/feed', [FeedController::class, 'author'])
    ->name('author.feed');
```

## Verification

```bash
# Access main feed
curl http://localhost/feed

# Validate XML
curl http://localhost/feed | xmllint --format -

# Check category feed
curl http://localhost/category/technology/feed
```

## Testing

```php
// tests/Feature/RssFeedTest.php

test('main feed returns valid rss', function () {
    Post::factory()->published()->count(5)->create();

    $response = $this->get('/feed');

    $response->assertStatus(200);
    $response->assertHeader('Content-Type', 'application/rss+xml; charset=UTF-8');
});

test('category feed only includes category posts', function () {
    $category = Category::factory()->create(['slug' => 'tech']);
    Post::factory()->published()->for($category)->count(3)->create();
    Post::factory()->published()->count(2)->create(); // Other category

    $response = $this->get('/category/tech/feed');

    $response->assertStatus(200);
    // Parse XML and verify only 3 items
});
```

## Next Steps

1. Add RSS icon to header/footer
2. Add category-specific feed links on category pages
3. Consider caching feed responses
4. Add featured images as enclosures
