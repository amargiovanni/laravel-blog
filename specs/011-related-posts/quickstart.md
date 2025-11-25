# Quickstart: Related Posts

## Prerequisites

- Laravel 12 application with Post model
- spatie/laravel-tags installed and configured
- Posts with tags and categories assigned

## Quick Implementation

### 1. Create RelatedPostsService

```bash
php artisan make:class Services/RelatedPostsService --no-interaction
```

```php
<?php

namespace App\Services;

use App\Models\Post;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class RelatedPostsService
{
    public function getRelatedPosts(Post $post, int $limit = 4): Collection
    {
        return Cache::remember(
            "related_posts:{$post->id}:{$limit}",
            now()->addHour(),
            fn() => $this->calculateRelatedPosts($post, $limit)
        );
    }

    protected function calculateRelatedPosts(Post $post, int $limit): Collection
    {
        $tagIds = $post->tags->pluck('id')->toArray();
        $categoryId = $post->category_id;

        // First, try to find posts with matching tags or category
        $related = Post::query()
            ->published()
            ->where('id', '!=', $post->id)
            ->where(function ($query) use ($tagIds, $categoryId) {
                if (!empty($tagIds)) {
                    $query->whereHas('tags', fn($q) => $q->whereIn('tags.id', $tagIds));
                }
                if ($categoryId) {
                    $query->orWhere('category_id', $categoryId);
                }
            })
            ->when(!empty($tagIds), function ($query) use ($tagIds) {
                $query->withCount(['tags as matching_tags_count' => function ($q) use ($tagIds) {
                    $q->whereIn('tags.id', $tagIds);
                }]);
            })
            ->orderByRaw('matching_tags_count DESC, published_at DESC')
            ->limit($limit)
            ->get();

        // Fill with recent posts if needed
        if ($related->count() < $limit) {
            $excludeIds = $related->pluck('id')->push($post->id);

            $filler = Post::query()
                ->published()
                ->whereNotIn('id', $excludeIds)
                ->latest('published_at')
                ->limit($limit - $related->count())
                ->get();

            $related = $related->concat($filler);
        }

        return $related;
    }

    public function clearCache(Post $post): void
    {
        Cache::forget("related_posts:{$post->id}:4");
        // Clear common limits
        foreach ([3, 4, 5, 6] as $limit) {
            Cache::forget("related_posts:{$post->id}:{$limit}");
        }
    }
}
```

### 2. Add Method to Post Model

```php
// app/Models/Post.php

use App\Services\RelatedPostsService;

public function relatedPosts(int $limit = 4): Collection
{
    return app(RelatedPostsService::class)->getRelatedPosts($this, $limit);
}
```

### 3. Create Blade Component

```bash
php artisan make:component RelatedPosts --no-interaction
```

```php
<?php

namespace App\View\Components;

use App\Models\Post;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class RelatedPosts extends Component
{
    public function __construct(
        public Post $post,
        public int $limit = 4
    ) {}

    public function render(): View|string
    {
        $relatedPosts = $this->post->relatedPosts($this->limit);

        if ($relatedPosts->isEmpty()) {
            return '';
        }

        return view('components.related-posts', [
            'posts' => $relatedPosts,
        ]);
    }
}
```

### 4. Create Component View

```blade
{{-- resources/views/components/related-posts.blade.php --}}

<section class="mt-12 border-t pt-8">
    <h3 class="text-2xl font-bold mb-6">Related Posts</h3>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        @foreach($posts as $post)
            <article class="group">
                <a href="{{ route('posts.show', $post->slug) }}" class="block">
                    @if($post->featured_image)
                        <img src="{{ asset($post->featured_image) }}"
                             alt="{{ $post->title }}"
                             class="w-full h-40 object-cover rounded-lg mb-3 group-hover:opacity-80 transition">
                    @else
                        <div class="w-full h-40 bg-gray-200 rounded-lg mb-3 flex items-center justify-center">
                            <span class="text-gray-400">No image</span>
                        </div>
                    @endif

                    <h4 class="font-semibold text-lg group-hover:text-blue-600 transition line-clamp-2">
                        {{ $post->title }}
                    </h4>

                    <time class="text-sm text-gray-500 mt-1 block">
                        {{ $post->published_at->format('M d, Y') }}
                    </time>
                </a>
            </article>
        @endforeach
    </div>
</section>
```

### 5. Add to Post View

```blade
{{-- In your single post view --}}

<article>
    <h1>{{ $post->title }}</h1>
    <div class="content">
        {!! $post->content !!}
    </div>
</article>

<x-related-posts :post="$post" :limit="4" />
```

### 6. Add Cache Invalidation

```php
// app/Models/Post.php

protected static function booted(): void
{
    static::saved(function (Post $post) {
        app(RelatedPostsService::class)->clearCache($post);
    });

    static::deleted(function (Post $post) {
        app(RelatedPostsService::class)->clearCache($post);
    });
}
```

## Verification

```php
// Test in tinker
$post = Post::with(['tags', 'category'])->published()->first();
$related = $post->relatedPosts();
$related->each(fn($p) => dump($p->title, $p->matching_tags_count ?? 0));
```

## Configuration Options

```php
// config/blog.php

return [
    'related_posts' => [
        'limit' => 4,
        'cache_ttl' => 3600, // 1 hour
        'tag_weight' => 3,
        'category_weight' => 1,
    ],
];
```

## Common Customizations

### Show Only Tag-Based Relations

```php
Post::query()
    ->published()
    ->where('id', '!=', $post->id)
    ->whereHas('tags', fn($q) => $q->whereIn('tags.id', $tagIds))
    ->limit($limit)
    ->get();
```

### Add Author Matching

```php
// Add author weight to scoring
$sameAuthor = $post->author_id === $relatedPost->author_id ? 2 : 0;
$score = ($matchingTags * 3) + $sameCategory + $sameAuthor;
```

### Exclude Specific Categories

```php
Post::query()
    ->published()
    ->whereNotIn('category_id', [1, 5]) // Exclude News, Announcements
    ->get();
```

## Next Steps

1. Tune relevance weights based on analytics
2. Add click tracking to measure effectiveness
3. Consider A/B testing different algorithms
4. Add admin configuration for limit/weights
