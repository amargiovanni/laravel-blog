# Route Contracts: XML Sitemap

## Public Routes

### GET /sitemap.xml

**Purpose**: Serve the XML sitemap for search engines

**Controller**: `SitemapController@index`

**Response**: XML (application/xml)

```xml
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">
    <url>
        <loc>https://example.com/</loc>
        <lastmod>2025-01-15T10:30:00+00:00</lastmod>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>
    <url>
        <loc>https://example.com/posts/my-first-post</loc>
        <lastmod>2025-01-14T08:00:00+00:00</lastmod>
        <changefreq>weekly</changefreq>
        <priority>0.8</priority>
        <image:image>
            <image:loc>https://example.com/images/featured.jpg</image:loc>
            <image:title>My First Post</image:title>
        </image:image>
    </url>
    <!-- More URLs... -->
</urlset>
```

**Status Codes**:
- 200: Sitemap generated successfully
- 500: Server error during generation

---

### GET /robots.txt

**Purpose**: Provide robots.txt with sitemap reference

**Response**: Plain text (text/plain)

```
User-agent: *
Allow: /
Disallow: /admin
Disallow: /admin/*

Sitemap: https://example.com/sitemap.xml
```

---

## Sitemap Index (Large Sites)

### GET /sitemap.xml (Index)

When site has > 50,000 URLs, returns sitemap index:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <sitemap>
        <loc>https://example.com/sitemap_posts.xml</loc>
        <lastmod>2025-01-15T10:30:00+00:00</lastmod>
    </sitemap>
    <sitemap>
        <loc>https://example.com/sitemap_pages.xml</loc>
        <lastmod>2025-01-15T10:30:00+00:00</lastmod>
    </sitemap>
    <sitemap>
        <loc>https://example.com/sitemap_categories.xml</loc>
        <lastmod>2025-01-15T10:30:00+00:00</lastmod>
    </sitemap>
</sitemapindex>
```

### GET /sitemap_posts.xml

Individual sitemap for posts only.

### GET /sitemap_pages.xml

Individual sitemap for pages only.

### GET /sitemap_categories.xml

Individual sitemap for categories only.

### GET /sitemap_tags.xml

Individual sitemap for tags only.

### GET /sitemap_authors.xml

Individual sitemap for author pages only.

---

## Artisan Commands

### sitemap:generate

**Purpose**: Generate static sitemap file(s)

**Usage**:
```bash
php artisan sitemap:generate
```

**Options**:
- `--index`: Force sitemap index generation even for small sites

**Output**:
```
Generating sitemap...
- Added 150 posts
- Added 12 pages
- Added 8 categories
- Added 25 tags
- Added 5 authors
Sitemap generated: public/sitemap.xml (200 URLs)
```

---

## URL Structure Summary

| Content | URL Pattern | Example |
|---------|-------------|---------|
| Homepage | `/` | `https://example.com/` |
| Post | `/posts/{slug}` | `https://example.com/posts/my-post` |
| Page | `/{slug}` | `https://example.com/about` |
| Category | `/category/{slug}` | `https://example.com/category/tech` |
| Tag | `/tag/{slug}` | `https://example.com/tag/laravel` |
| Author | `/author/{id}` | `https://example.com/author/1` |

---

## Route Registration

```php
// routes/web.php

// Sitemap
Route::get('sitemap.xml', [SitemapController::class, 'index'])
    ->name('sitemap');

// Optional: Dynamic robots.txt
Route::get('robots.txt', [SitemapController::class, 'robots'])
    ->name('robots');
```

## Middleware

No authentication required - these routes must be publicly accessible to search engine bots.

## Caching

Response can be cached at the web server level (nginx/apache) or via Laravel response caching for optimal performance.
