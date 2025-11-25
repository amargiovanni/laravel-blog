# Data Model: XML Sitemap

## Overview

The XML Sitemap feature does not require new database tables. It reads from existing content models and generates XML output.

## Source Models

### Post Model (Existing)

```
┌─────────────────────────────────────┐
│              posts                  │
├─────────────────────────────────────┤
│ id              │ bigint PK         │
│ title           │ string            │
│ slug            │ string (unique)   │
│ status          │ enum (published)  │
│ published_at    │ timestamp         │
│ updated_at      │ timestamp         │
│ featured_image  │ string (nullable) │
└─────────────────────────────────────┘
```

**Sitemap Query**: `Post::where('status', 'published')->whereNotNull('published_at')`

### Page Model (From 003-static-pages)

```
┌─────────────────────────────────────┐
│              pages                  │
├─────────────────────────────────────┤
│ id              │ bigint PK         │
│ title           │ string            │
│ slug            │ string (unique)   │
│ status          │ enum (published)  │
│ published_at    │ timestamp         │
│ updated_at      │ timestamp         │
└─────────────────────────────────────┘
```

**Sitemap Query**: `Page::where('status', 'published')`

### Category Model (Existing)

```
┌─────────────────────────────────────┐
│           categories                │
├─────────────────────────────────────┤
│ id              │ bigint PK         │
│ name            │ string            │
│ slug            │ string (unique)   │
│ updated_at      │ timestamp         │
└─────────────────────────────────────┘
```

**Sitemap Query**: `Category::whereHas('posts', fn($q) => $q->published())`

### Tag Model (Existing)

```
┌─────────────────────────────────────┐
│              tags                   │
├─────────────────────────────────────┤
│ id              │ bigint PK         │
│ name            │ json              │
│ slug            │ json              │
│ type            │ string (nullable) │
│ updated_at      │ timestamp         │
└─────────────────────────────────────┘
```

**Sitemap Query**: `Tag::whereHas('posts', fn($q) => $q->published())`

### User Model (Existing)

```
┌─────────────────────────────────────┐
│              users                  │
├─────────────────────────────────────┤
│ id              │ bigint PK         │
│ name            │ string            │
│ email           │ string            │
│ updated_at      │ timestamp         │
└─────────────────────────────────────┘
```

**Sitemap Query**: `User::whereHas('posts', fn($q) => $q->published())`

## Sitemap URL Structure

### URL Entry Schema (XML)

```xml
<url>
    <loc>https://example.com/posts/my-post</loc>
    <lastmod>2025-01-15T10:30:00+00:00</lastmod>
    <changefreq>weekly</changefreq>
    <priority>0.8</priority>
    <image:image>
        <image:loc>https://example.com/images/featured.jpg</image:loc>
        <image:title>Featured Image</image:title>
    </image:image>
</url>
```

## Priority and Frequency Matrix

| Content Type | Priority | Change Frequency | Notes |
|--------------|----------|------------------|-------|
| Homepage | 1.0 | daily | Most important |
| Blog Index | 0.9 | daily | Frequently updated |
| Posts | 0.8 | weekly | Main content |
| Pages | 0.7 | monthly | Static content |
| Categories | 0.6 | weekly | Archive pages |
| Tags | 0.5 | weekly | Archive pages |
| Authors | 0.5 | weekly | Archive pages |

## Sitemap File Structure

### Single Sitemap (< 50,000 URLs)

```
public/
└── sitemap.xml          # Main sitemap with all URLs
```

### Sitemap Index (> 50,000 URLs)

```
public/
├── sitemap.xml          # Sitemap index
├── sitemap_posts.xml    # Posts sitemap
├── sitemap_pages.xml    # Pages sitemap
├── sitemap_categories.xml
├── sitemap_tags.xml
└── sitemap_authors.xml
```

## Cache Strategy

### Cache Keys

| Key | TTL | Description |
|-----|-----|-------------|
| `sitemap:generated_at` | - | Timestamp of last generation |
| `sitemap:urls_count` | - | Total URLs in sitemap |

### Cache Invalidation Events

```php
// Events that trigger sitemap regeneration
Post::created, Post::updated, Post::deleted
Page::created, Page::updated, Page::deleted
Category::created, Category::updated, Category::deleted
Tag::created, Tag::updated, Tag::deleted
```

## No Database Migrations Required

This feature operates on read-only access to existing models and generates XML files to the filesystem. No new database tables or columns are needed.
