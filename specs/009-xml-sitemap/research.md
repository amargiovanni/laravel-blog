# Research: XML Sitemap

## Package Analysis: spatie/laravel-sitemap

### Overview

The `spatie/laravel-sitemap` package provides a fluent interface for generating XML sitemaps in Laravel applications. It handles the complexity of sitemap protocol compliance, automatic URL encoding, and sitemap indexing for large sites.

### Key Features

1. **Sitemap Generation**
   - Generate sitemaps programmatically or by crawling
   - Support for all sitemap protocol attributes (lastmod, changefreq, priority)
   - Automatic XML encoding of special characters
   - Image sitemap extension support

2. **Sitemap Index Support**
   - Automatic splitting when exceeding 50,000 URLs
   - Sitemap index file generation
   - Multiple sitemap files organization

3. **Integration Options**
   - Can add URLs manually via `Sitemap::create()->add()`
   - Can crawl website automatically via `Sitemap::create()->crawl()`
   - Support for custom URL generators

### Installation

```bash
composer require spatie/laravel-sitemap
```

### Basic Usage

```php
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

// Create sitemap with custom URLs
$sitemap = Sitemap::create()
    ->add(Url::create('/posts')
        ->setLastModificationDate(Carbon::yesterday())
        ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
        ->setPriority(0.8))
    ->writeToFile(public_path('sitemap.xml'));

// Or generate dynamically
Sitemap::create()
    ->add(Post::published()->get())
    ->writeToFile(public_path('sitemap.xml'));
```

### Sitemap Index for Large Sites

```php
use Spatie\Sitemap\SitemapIndex;

SitemapIndex::create()
    ->add('/sitemap_posts.xml')
    ->add('/sitemap_pages.xml')
    ->add('/sitemap_categories.xml')
    ->writeToFile(public_path('sitemap.xml'));
```

### Change Frequencies

Available constants:
- `Url::CHANGE_FREQUENCY_ALWAYS`
- `Url::CHANGE_FREQUENCY_HOURLY`
- `Url::CHANGE_FREQUENCY_DAILY`
- `Url::CHANGE_FREQUENCY_WEEKLY`
- `Url::CHANGE_FREQUENCY_MONTHLY`
- `Url::CHANGE_FREQUENCY_YEARLY`
- `Url::CHANGE_FREQUENCY_NEVER`

### Image Sitemap Support

```php
use Spatie\Sitemap\Tags\Url;

Url::create('/post/1')
    ->addImage('/images/post-1.jpg', 'Post 1 Image');
```

## Existing Codebase Analysis

### Models to Include

1. **Post** (`app/Models/Post.php`)
   - Include: published posts only
   - URL: `/posts/{slug}`
   - Lastmod: `updated_at`
   - Changefreq: weekly
   - Priority: 0.8

2. **Page** (`app/Models/Page.php`) - From 003-static-pages
   - Include: published pages only
   - URL: `/{slug}`
   - Lastmod: `updated_at`
   - Changefreq: monthly
   - Priority: 0.7

3. **Category** (`app/Models/Category.php`)
   - Include: categories with published posts
   - URL: `/category/{slug}`
   - Lastmod: latest post's `updated_at`
   - Changefreq: weekly
   - Priority: 0.6

4. **Tag** (`app/Models/Tag.php`)
   - Include: tags with published posts
   - URL: `/tag/{slug}`
   - Lastmod: latest post's `updated_at`
   - Changefreq: weekly
   - Priority: 0.5

5. **User/Author** (`app/Models/User.php`)
   - Include: authors with published posts
   - URL: `/author/{slug}` or `/author/{id}`
   - Lastmod: latest post's `updated_at`
   - Changefreq: weekly
   - Priority: 0.5

### Static Pages to Include

- Homepage (`/`) - Priority: 1.0, Changefreq: daily
- Blog index (`/blog` or `/posts`) - Priority: 0.9, Changefreq: daily

## Implementation Strategy

### Option 1: Dynamic Generation (Recommended for small-medium sites)

Generate sitemap on each request using route and controller:

```php
Route::get('sitemap.xml', [SitemapController::class, 'index']);
```

**Pros:**
- Always up-to-date
- No storage needed
- Simple implementation

**Cons:**
- Performance overhead on each request
- Not suitable for large sites

### Option 2: Cached/Static Generation (Recommended for this project)

Generate sitemap via artisan command and serve static file:

```php
// Artisan command
php artisan sitemap:generate

// Trigger on content changes via model observers or events
```

**Pros:**
- Excellent performance
- Suitable for any site size
- Can be scheduled

**Cons:**
- Slight delay in updates
- Requires cache invalidation strategy

### Recommended: Hybrid Approach

1. Generate static sitemap file via artisan command
2. Cache invalidation on content publish/update events
3. Serve static file for best performance
4. Schedule regeneration as backup

## robots.txt Integration

Add sitemap reference to `public/robots.txt`:

```
User-agent: *
Allow: /

Sitemap: https://example.com/sitemap.xml
```

Or generate dynamically:

```php
Route::get('robots.txt', function () {
    $sitemap = url('sitemap.xml');
    return response("User-agent: *\nAllow: /\n\nSitemap: {$sitemap}")
        ->header('Content-Type', 'text/plain');
});
```

## Testing Considerations

1. **XML Validation**: Verify output is valid XML and conforms to sitemap protocol
2. **URL Inclusion**: Test all content types are included
3. **URL Exclusion**: Test drafts/private content are excluded
4. **Metadata**: Verify lastmod, changefreq, priority values
5. **Large Sites**: Test sitemap index generation with many URLs
6. **Special Characters**: Test URLs with special characters are properly encoded

## Dependencies Decision

| Package | Purpose | Recommendation |
|---------|---------|----------------|
| spatie/laravel-sitemap | Core sitemap generation | **Install** |

## Risk Assessment

- **Low Risk**: Standard package with extensive Laravel community usage
- **Performance**: Cached generation mitigates any performance concerns
- **Maintenance**: Spatie packages are well-maintained and updated regularly
