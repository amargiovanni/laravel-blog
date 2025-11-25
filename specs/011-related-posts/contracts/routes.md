# Contracts: Related Posts

## Component Contract

### RelatedPosts Component

**Location**: `app/View/Components/RelatedPosts.php`

**Purpose**: Display related posts on single post pages

**Parameters**:

| Parameter | Type | Required | Default | Description |
|-----------|------|----------|---------|-------------|
| post | Post | Yes | - | The current post to find relations for |
| limit | int | No | 4 | Maximum number of related posts |

**Usage**:

```blade
<x-related-posts :post="$post" />

<x-related-posts :post="$post" :limit="6" />
```

**Output**:
- Renders related posts grid when posts exist
- Returns empty string when no related posts found

---

## Service Contract

### RelatedPostsService

**Location**: `app/Services/RelatedPostsService.php`

**Interface**:

```php
interface RelatedPostsServiceInterface
{
    /**
     * Get related posts for a given post
     *
     * @param Post $post The source post
     * @param int $limit Maximum posts to return (default: 4)
     * @return Collection<Post> Collection of related Post models
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

**Behavior**:

1. Checks cache first (`related_posts:{post_id}:{limit}`)
2. If miss, calculates related posts using:
   - Tag matching (3 points per shared tag)
   - Category matching (1 point for same category)
3. Falls back to recent posts if insufficient matches
4. Caches result for 1 hour
5. Returns Collection of Post models

---

## Model Contract

### Post Model Extension

**Method**: `relatedPosts(int $limit = 4): Collection`

**Location**: `app/Models/Post.php`

**Usage**:

```php
$post = Post::find(1);
$related = $post->relatedPosts(); // Default 4 posts
$related = $post->relatedPosts(6); // Custom limit
```

**Returns**: Collection of Post models

---

## View Contract

### related-posts.blade.php

**Location**: `resources/views/components/related-posts.blade.php`

**Required Variables**:

| Variable | Type | Description |
|----------|------|-------------|
| posts | Collection | Collection of Post models |

**Expected Post Properties**:

| Property | Type | Required | Description |
|----------|------|----------|-------------|
| title | string | Yes | Post title |
| slug | string | Yes | Post slug for URL |
| featured_image | string|null | No | Path to featured image |
| published_at | Carbon | Yes | Publication date |

**HTML Structure**:

```html
<section class="related-posts">
    <h3>Related Posts</h3>
    <div class="grid">
        @foreach($posts as $post)
            <article>
                <a href="...">
                    <img src="..." alt="...">
                    <h4>{{ $post->title }}</h4>
                    <time>{{ $post->published_at }}</time>
                </a>
            </article>
        @endforeach
    </div>
</section>
```

---

## Cache Contract

### Cache Keys

| Key Pattern | TTL | Description |
|-------------|-----|-------------|
| `related_posts:{post_id}:{limit}` | 3600s | Cached related posts |

### Invalidation Events

| Event | Action |
|-------|--------|
| Post saved | Clear cache for that post |
| Post deleted | Clear cache for that post |
| Post tags synced | Clear cache for that post |

---

## Configuration (Optional)

### config/blog.php

```php
return [
    'related_posts' => [
        'enabled' => true,
        'limit' => 4,
        'cache_ttl' => 3600,
        'weights' => [
            'tag' => 3,
            'category' => 1,
        ],
        'fallback_to_recent' => true,
    ],
];
```

---

## Display Locations

| Location | Condition | Layout |
|----------|-----------|--------|
| Single Post Page | Always (if posts exist) | Grid below content |
| Static Pages | Never | N/A |
| Category Archives | Optional | N/A |

---

## No Routes Required

This feature is implemented as a Blade component and does not require dedicated routes. It's embedded in existing post views.
