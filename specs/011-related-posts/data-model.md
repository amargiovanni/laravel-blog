# Data Model: Related Posts

## Overview

The Related Posts feature computes relevance scores using existing post relationships. No new database tables are required.

## Source Models

### Post Model (Existing)

```
┌─────────────────────────────────────┐
│              posts                  │
├─────────────────────────────────────┤
│ id              │ bigint PK         │
│ title           │ string            │
│ slug            │ string (unique)   │
│ status          │ enum              │
│ published_at    │ timestamp         │
│ featured_image  │ string (nullable) │
│ category_id     │ bigint FK         │
└─────────────────────────────────────┘
```

### Taggables (spatie/laravel-tags)

```
┌─────────────────────────────────────┐
│            taggables                │
├─────────────────────────────────────┤
│ tag_id          │ bigint            │
│ taggable_type   │ string            │
│ taggable_id     │ bigint            │
└─────────────────────────────────────┘
```

### Category Model (Existing)

```
┌─────────────────────────────────────┐
│           categories                │
├─────────────────────────────────────┤
│ id              │ bigint PK         │
│ name            │ string            │
│ slug            │ string (unique)   │
└─────────────────────────────────────┘
```

## Relevance Scoring

### Score Formula

```
relevance_score = (shared_tags × 3) + (same_category × 1) + recency_bonus
```

### Weight Distribution

| Factor | Points | Description |
|--------|--------|-------------|
| Shared Tag | 3 per tag | Each matching tag adds 3 points |
| Same Category | 1 | Single point for category match |
| Recency Bonus | 0-1 | Decay based on age |

### Recency Bonus Calculation

```php
// Posts within 30 days get bonus
$daysSincePublished = $post->published_at->diffInDays(now());
$recencyBonus = max(0, 1 - ($daysSincePublished / 30));
```

### Score Examples

| Post | Shared Tags | Same Category | Days Old | Score |
|------|-------------|---------------|----------|-------|
| A | 3 | Yes | 5 | 9 + 1 + 0.83 = 10.83 |
| B | 2 | Yes | 15 | 6 + 1 + 0.5 = 7.5 |
| C | 1 | No | 2 | 3 + 0 + 0.93 = 3.93 |
| D | 0 | Yes | 60 | 0 + 1 + 0 = 1.0 |

## Query Structure

### Main Query

```sql
SELECT posts.*,
       (
           SELECT COUNT(*) FROM taggables t1
           WHERE t1.taggable_id = posts.id
           AND t1.taggable_type = 'App\\Models\\Post'
           AND t1.tag_id IN (
               SELECT t2.tag_id FROM taggables t2
               WHERE t2.taggable_id = :current_post_id
               AND t2.taggable_type = 'App\\Models\\Post'
           )
       ) * 3 +
       CASE WHEN posts.category_id = :category_id THEN 1 ELSE 0 END
       AS relevance_score
FROM posts
WHERE posts.id != :current_post_id
  AND posts.status = 'published'
  AND posts.published_at <= NOW()
HAVING relevance_score > 0
ORDER BY relevance_score DESC, posts.published_at DESC
LIMIT 4;
```

### Eloquent Implementation

```php
Post::query()
    ->published()
    ->where('id', '!=', $post->id)
    ->where(function ($query) use ($tagIds, $categoryId) {
        $query->whereHas('tags', fn($q) => $q->whereIn('tags.id', $tagIds))
              ->orWhere('category_id', $categoryId);
    })
    ->withCount(['tags as matching_tags_count' => function ($query) use ($tagIds) {
        $query->whereIn('tags.id', $tagIds);
    }])
    ->selectRaw('*, (matching_tags_count * 3 + IF(category_id = ?, 1, 0)) as relevance_score', [$categoryId])
    ->orderByDesc('relevance_score')
    ->orderByDesc('published_at')
    ->limit(4);
```

## Cache Structure

### Cache Key

```
related_posts:{post_id}:{limit}
```

Example: `related_posts:42:4`

### Cache Value

```php
// Stored as serialized Collection of Post models
[
    Post { id: 15, title: "...", slug: "...", ... },
    Post { id: 23, title: "...", slug: "...", ... },
    Post { id: 8, title: "...", slug: "...", ... },
    Post { id: 31, title: "...", slug: "...", ... },
]
```

### Cache TTL

| Environment | TTL |
|-------------|-----|
| Production | 3600 seconds (1 hour) |
| Development | 60 seconds (1 minute) |

### Cache Invalidation Events

| Event | Action |
|-------|--------|
| Post created | No action (new post has no relations) |
| Post updated | Clear `related_posts:{post_id}:*` |
| Post deleted | Clear `related_posts:{post_id}:*` |
| Post tags changed | Clear cache for post AND posts with shared tags |
| Post category changed | Clear cache for post |

## Service Interface

```php
interface RelatedPostsServiceInterface
{
    /**
     * Get related posts for a given post
     *
     * @param Post $post The source post
     * @param int $limit Maximum posts to return
     * @return Collection<Post>
     */
    public function getRelatedPosts(Post $post, int $limit = 4): Collection;

    /**
     * Clear cached related posts for a post
     *
     * @param Post $post The post to clear cache for
     * @return void
     */
    public function clearCache(Post $post): void;
}
```

## Component Data Flow

```
┌─────────────┐     ┌──────────────────────┐     ┌─────────┐
│ Post View   │────▶│ RelatedPostsService  │────▶│  Cache  │
└─────────────┘     └──────────────────────┘     └─────────┘
                              │                       │
                              │ (cache miss)          │ (cache hit)
                              ▼                       │
                    ┌──────────────────┐              │
                    │ Database Query   │              │
                    └──────────────────┘              │
                              │                       │
                              ▼                       ▼
                    ┌──────────────────────────────────┐
                    │      Related Posts Collection    │
                    └──────────────────────────────────┘
                              │
                              ▼
                    ┌──────────────────────────────────┐
                    │    RelatedPosts Component        │
                    └──────────────────────────────────┘
```

## No Database Migrations Required

This feature uses existing models and relationships. Relevance scores are computed at query time and cached.
