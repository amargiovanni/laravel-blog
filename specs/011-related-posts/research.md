# Research: Related Posts

## Algorithm Approaches

### 1. Tag-Based Matching

The most common and effective approach for finding related content.

**Algorithm**:
```php
// Find posts sharing tags with current post
$tagIds = $post->tags->pluck('id');

$relatedPosts = Post::query()
    ->published()
    ->where('id', '!=', $post->id)
    ->whereHas('tags', fn($q) => $q->whereIn('tags.id', $tagIds))
    ->withCount(['tags' => fn($q) => $q->whereIn('tags.id', $tagIds)])
    ->orderByDesc('tags_count')
    ->orderByDesc('published_at')
    ->limit(4)
    ->get();
```

**Pros**:
- High relevance (tags are specific)
- Easy to implement
- Fast query performance

**Cons**:
- Requires well-tagged posts
- May return no results for poorly tagged posts

### 2. Category-Based Matching

Broader matching using category relationships.

**Algorithm**:
```php
$relatedPosts = Post::query()
    ->published()
    ->where('id', '!=', $post->id)
    ->where('category_id', $post->category_id)
    ->latest('published_at')
    ->limit(4)
    ->get();
```

**Pros**:
- Always returns results (if category has posts)
- Simple implementation

**Cons**:
- Lower relevance (categories are broad)
- May return unrelated posts

### 3. Combined Scoring (Recommended)

Combines tag and category matching with weighted scoring.

**Scoring Formula**:
```
score = (shared_tags * 3) + (same_category * 1) + (recency_bonus * 0.5)
```

Where:
- `shared_tags`: Number of matching tags (0-N)
- `same_category`: 1 if same category, 0 otherwise
- `recency_bonus`: Decay based on publication date (0-1)

**Algorithm**:
```php
public function getRelatedPosts(Post $post, int $limit = 4): Collection
{
    $tagIds = $post->tags->pluck('id');
    $categoryId = $post->category_id;

    return Post::query()
        ->published()
        ->where('id', '!=', $post->id)
        ->where(function ($query) use ($tagIds, $categoryId) {
            $query->whereHas('tags', fn($q) => $q->whereIn('tags.id', $tagIds))
                  ->orWhere('category_id', $categoryId);
        })
        ->withCount(['tags' => fn($q) => $q->whereIn('tags.id', $tagIds)])
        ->selectRaw('*,
            (tags_count * 3 + IF(category_id = ?, 1, 0)) as relevance_score',
            [$categoryId]
        )
        ->orderByDesc('relevance_score')
        ->orderByDesc('published_at')
        ->limit($limit)
        ->get();
}
```

### 4. Content-Based (TF-IDF)

Advanced approach using text analysis.

**Not Recommended For This Project** because:
- Requires significant processing
- Better suited for search engines (already have 008-fulltext-search)
- Overkill for small-medium blogs

## Caching Strategy

### Cache Key Structure

```php
$cacheKey = "related_posts:{$post->id}";
```

### Cache Duration

- **Recommended**: 1 hour (3600 seconds)
- Balance between freshness and performance

### Cache Invalidation

Clear cache when:
1. Post is updated (tags/categories changed)
2. Post is published/unpublished
3. Related post is deleted

```php
// In Post model boot method or observer
protected static function booted(): void
{
    static::saved(function (Post $post) {
        Cache::forget("related_posts:{$post->id}");
        // Also clear cache for posts that might reference this one
    });
}
```

### Cache Implementation

```php
public function getRelatedPosts(Post $post, int $limit = 4): Collection
{
    return Cache::remember(
        "related_posts:{$post->id}:{$limit}",
        now()->addHour(),
        fn() => $this->calculateRelatedPosts($post, $limit)
    );
}
```

## Performance Considerations

### Query Optimization

1. **Index Requirements**:
   - `posts.category_id`
   - `posts.status`
   - `posts.published_at`
   - `taggables.tag_id`, `taggables.taggable_id`

2. **Eager Loading**:
   ```php
   Post::with(['tags', 'category'])->find($id);
   ```

3. **Limit Results Early**:
   ```php
   ->limit($limit) // Apply in query, not in PHP
   ```

### Benchmark Targets

| Operation | Target | Acceptable |
|-----------|--------|------------|
| Uncached query | < 50ms | < 100ms |
| Cached response | < 10ms | < 20ms |
| Cache hit rate | > 95% | > 90% |

## Fallback Strategy

### Priority Order

1. Posts matching multiple tags
2. Posts matching single tag
3. Posts in same category
4. Most recent posts (any category)

### Implementation

```php
public function getRelatedPosts(Post $post, int $limit = 4): Collection
{
    $related = $this->calculateRelatedPosts($post, $limit);

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
```

## View Component Design

### Blade Component

```php
// app/View/Components/RelatedPosts.php
class RelatedPosts extends Component
{
    public function __construct(
        public Post $post,
        public int $limit = 4
    ) {}

    public function render()
    {
        $relatedPosts = app(RelatedPostsService::class)
            ->getRelatedPosts($this->post, $this->limit);

        if ($relatedPosts->isEmpty()) {
            return '';
        }

        return view('components.related-posts', [
            'posts' => $relatedPosts,
        ]);
    }
}
```

### Usage

```blade
<x-related-posts :post="$post" :limit="4" />
```

## WordPress Comparison

| Feature | WordPress | Our Implementation |
|---------|-----------|-------------------|
| Tag matching | ✅ (YARPP plugin) | ✅ |
| Category matching | ✅ | ✅ |
| Content matching | ✅ (YARPP) | ❌ (Not needed) |
| Caching | ✅ (Plugin dependent) | ✅ (Laravel Cache) |
| Configuration | Admin settings | Config/Constants |
| Performance | Variable | Optimized |

## Recommendation

Use **Combined Scoring** approach with:
- Tag weight: 3 points per shared tag
- Category weight: 1 point for same category
- Tiebreaker: Most recent first
- Fallback: Recent posts fill remaining slots
- Cache: 1 hour TTL with event-based invalidation
