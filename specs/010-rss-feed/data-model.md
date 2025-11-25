# Data Model: RSS Feed

## Overview

The RSS Feed feature generates XML feeds from existing content models. No new database tables are required.

## Source Models

### Post Model (Existing)

```
┌─────────────────────────────────────┐
│              posts                  │
├─────────────────────────────────────┤
│ id              │ bigint PK         │
│ title           │ string            │
│ slug            │ string (unique)   │
│ content         │ text              │
│ excerpt         │ text (nullable)   │
│ status          │ enum (published)  │
│ published_at    │ timestamp         │
│ updated_at      │ timestamp         │
│ featured_image  │ string (nullable) │
│ author_id       │ bigint FK         │
│ category_id     │ bigint FK         │
└─────────────────────────────────────┘
```

**Feed Query**: `Post::published()->with(['author', 'category', 'tags'])->latest('published_at')->take(20)`

### Category Model (Existing)

```
┌─────────────────────────────────────┐
│           categories                │
├─────────────────────────────────────┤
│ id              │ bigint PK         │
│ name            │ string            │
│ slug            │ string (unique)   │
│ description     │ text (nullable)   │
└─────────────────────────────────────┘
```

**Feed Query**: Category lookup by slug for category-specific feeds.

### User/Author Model (Existing)

```
┌─────────────────────────────────────┐
│              users                  │
├─────────────────────────────────────┤
│ id              │ bigint PK         │
│ name            │ string            │
│ email           │ string            │
└─────────────────────────────────────┘
```

**Feed Query**: User lookup by ID for author-specific feeds.

## RSS Feed Structure

### Channel (Feed) Schema

```xml
<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
    <channel>
        <title>Blog Name</title>
        <link>https://example.com</link>
        <description>Blog description</description>
        <language>en-US</language>
        <lastBuildDate>Wed, 15 Jan 2025 10:30:00 +0000</lastBuildDate>
        <atom:link href="https://example.com/feed" rel="self" type="application/rss+xml"/>

        <item>...</item>
        <item>...</item>
    </channel>
</rss>
```

### Item (Post) Schema

```xml
<item>
    <title>Post Title</title>
    <link>https://example.com/posts/post-slug</link>
    <description><![CDATA[
        <p>Post content or excerpt...</p>
    ]]></description>
    <pubDate>Mon, 13 Jan 2025 08:00:00 +0000</pubDate>
    <author>author@example.com (Author Name)</author>
    <guid isPermaLink="true">https://example.com/posts/post-slug</guid>
    <category>Technology</category>
    <category>Laravel</category>
    <enclosure url="https://example.com/images/featured.jpg"
               length="12345"
               type="image/jpeg"/>
</item>
```

## Field Mapping

### Post to Feed Item

| Post Field | RSS Element | Notes |
|------------|-------------|-------|
| `title` | `<title>` | Required |
| `slug` | `<link>`, `<guid>` | Build URL with route() |
| `excerpt` or `content` | `<description>` | Prefer excerpt, truncate content |
| `published_at` | `<pubDate>` | RFC 2822 format |
| `author.name` | `<author>` | Format: "email (name)" |
| `category.name` | `<category>` | Multiple allowed |
| `tags.*.name` | `<category>` | Multiple allowed |
| `featured_image` | `<enclosure>` | With MIME type |

### Feed Metadata Mapping

| Source | RSS Element | Value |
|--------|-------------|-------|
| Settings | `<title>` | Blog name |
| Settings | `<description>` | Blog tagline |
| Config | `<link>` | APP_URL |
| Config | `<language>` | app.locale |
| Generated | `<lastBuildDate>` | Most recent post date |

## Feed Types

### Main Feed

| Property | Value |
|----------|-------|
| URL | `/feed` |
| Title | "{Blog Name}" |
| Description | "{Blog Tagline}" |
| Items | All published posts |
| Limit | 20 items |

### Category Feed

| Property | Value |
|----------|-------|
| URL | `/category/{slug}/feed` |
| Title | "{Category Name} - {Blog Name}" |
| Description | Category description |
| Items | Posts in category |
| Limit | 20 items |

### Author Feed

| Property | Value |
|----------|-------|
| URL | `/author/{id}/feed` |
| Title | "Posts by {Author Name} - {Blog Name}" |
| Description | Author bio (if available) |
| Items | Posts by author |
| Limit | 20 items |

## Feedable Interface Implementation

```php
// app/Models/Post.php

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
            ->authorName($this->author->name)
            ->authorEmail($this->author->email)
            ->category($this->category->name);
    }

    public static function getFeedItems()
    {
        return static::query()
            ->published()
            ->with(['author', 'category', 'tags'])
            ->latest('published_at')
            ->take(20)
            ->get();
    }
}
```

## Configuration (config/feed.php)

```php
return [
    'feeds' => [
        'main' => [
            'items' => ['App\Models\Post', 'getFeedItems'],
            'url' => '/feed',
            'title' => config('app.name') . ' RSS Feed',
            'description' => 'Latest posts from ' . config('app.name'),
            'language' => config('app.locale'),
            'format' => 'rss',
            'view' => 'feed::rss',
        ],
    ],
];
```

## No Database Migrations Required

This feature reads from existing models and outputs XML. No schema changes needed.
