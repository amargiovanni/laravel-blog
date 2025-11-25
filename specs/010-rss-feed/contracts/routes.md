# Route Contracts: RSS Feed

## Public Routes

### GET /feed

**Purpose**: Main blog RSS feed with all published posts

**Controller**: Auto-registered by spatie/laravel-feed package

**Response**: XML (application/rss+xml)

```xml
<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
    <channel>
        <title>Blog Name RSS Feed</title>
        <link>https://example.com</link>
        <description>Latest posts from Blog Name</description>
        <language>en-US</language>
        <lastBuildDate>Wed, 15 Jan 2025 10:30:00 +0000</lastBuildDate>
        <atom:link href="https://example.com/feed" rel="self" type="application/rss+xml"/>

        <item>
            <title>My Latest Post</title>
            <link>https://example.com/posts/my-latest-post</link>
            <description><![CDATA[Post excerpt or content...]]></description>
            <pubDate>Wed, 15 Jan 2025 08:00:00 +0000</pubDate>
            <author>author@example.com (Author Name)</author>
            <guid isPermaLink="true">https://example.com/posts/my-latest-post</guid>
            <category>Technology</category>
        </item>
        <!-- More items... -->
    </channel>
</rss>
```

**Status Codes**:
- 200: Feed generated successfully

---

### GET /category/{slug}/feed

**Purpose**: RSS feed for a specific category

**Controller**: `FeedController@category`

**Parameters**:
- `slug` (string, required): Category slug

**Response**: Same XML structure as main feed with filtered items

**Example**: `/category/technology/feed`

**Feed Title Format**: "{Category Name} - {Blog Name}"

**Status Codes**:
- 200: Feed generated successfully
- 404: Category not found

---

### GET /author/{id}/feed

**Purpose**: RSS feed for a specific author

**Controller**: `FeedController@author`

**Parameters**:
- `id` (integer, required): Author user ID

**Response**: Same XML structure as main feed with filtered items

**Example**: `/author/1/feed`

**Feed Title Format**: "Posts by {Author Name} - {Blog Name}"

**Status Codes**:
- 200: Feed generated successfully
- 404: Author not found

---

## HTTP Headers

All feed endpoints return these headers:

| Header | Value |
|--------|-------|
| Content-Type | application/rss+xml; charset=UTF-8 |
| Cache-Control | public, max-age=3600 |

---

## Auto-Discovery

### HTML Link Tag

Added to all public pages in `<head>`:

```html
<link rel="alternate"
      type="application/rss+xml"
      title="Blog Name RSS Feed"
      href="https://example.com/feed">
```

### Category Page Link Tag

On category archive pages, also include:

```html
<link rel="alternate"
      type="application/rss+xml"
      title="Technology - Blog Name RSS Feed"
      href="https://example.com/category/technology/feed">
```

---

## Route Registration

```php
// routes/web.php

// Main feed - auto-registered by package via config/feed.php

// Category feed
Route::get('category/{slug}/feed', [FeedController::class, 'category'])
    ->name('category.feed');

// Author feed
Route::get('author/{id}/feed', [FeedController::class, 'author'])
    ->name('author.feed');
```

---

## Feed Configuration

```php
// config/feed.php

return [
    'feeds' => [
        'main' => [
            'items' => ['App\Models\Post', 'getFeedItems'],
            'url' => '/feed',
            'title' => 'Blog Name RSS Feed',
            'description' => 'Latest posts from Blog Name',
            'language' => 'en-US',
            'format' => 'rss',
            'view' => 'feed::rss',
        ],
    ],
];
```

---

## Content Limits

| Feed Type | Item Limit | Description |
|-----------|------------|-------------|
| Main | 20 | Most recent published posts |
| Category | 20 | Most recent in category |
| Author | 20 | Most recent by author |

---

## Middleware

No authentication required - all feed routes are publicly accessible for feed reader access.
