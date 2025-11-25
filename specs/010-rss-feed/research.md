# Research: RSS Feed

## Package Analysis: spatie/laravel-feed

### Overview

The `spatie/laravel-feed` package provides a clean API for generating RSS and Atom feeds in Laravel applications. It handles XML generation, content encoding, and supports multiple feed formats.

### Key Features

1. **Multiple Feed Formats**
   - RSS 2.0 (default)
   - Atom
   - JSON Feed

2. **Flexible Configuration**
   - Configure feeds via config file
   - Dynamic feed generation via controllers
   - Custom feed item properties

3. **Feedable Interface**
   - Models implement `Feedable` interface
   - Simple `toFeedItem()` method
   - Automatic property mapping

### Installation

```bash
composer require spatie/laravel-feed
php artisan vendor:publish --tag=feed-config
```

### Configuration (config/feed.php)

```php
return [
    'feeds' => [
        'main' => [
            'items' => ['App\Models\Post', 'getFeedItems'],
            'url' => '/feed',
            'title' => 'My Blog Feed',
            'description' => 'Latest posts from my blog',
            'language' => 'en-US',
            'image' => '',
            'format' => 'rss',
            'view' => 'feed::rss',
        ],
    ],
];
```

### Model Implementation

```php
use Spatie\Feed\Feedable;
use Spatie\Feed\FeedItem;

class Post extends Model implements Feedable
{
    public function toFeedItem(): FeedItem
    {
        return FeedItem::create()
            ->id($this->id)
            ->title($this->title)
            ->summary($this->excerpt)
            ->updated($this->updated_at)
            ->link(route('posts.show', $this->slug))
            ->authorName($this->author->name);
    }

    public static function getFeedItems()
    {
        return static::published()
            ->latest('published_at')
            ->take(20)
            ->get();
    }
}
```

### Dynamic Feeds via Controller

```php
use Spatie\Feed\Feed;
use Spatie\Feed\FeedItem;

class FeedController extends Controller
{
    public function category(string $slug)
    {
        $category = Category::where('slug', $slug)->firstOrFail();

        $posts = Post::published()
            ->where('category_id', $category->id)
            ->latest('published_at')
            ->take(20)
            ->get();

        return new Feed(
            "{$category->name} - Blog Feed",
            $posts,
            route('category.feed', $slug),
            'feed::rss'
        );
    }
}
```

## RSS 2.0 Specification

### Required Channel Elements

| Element | Description | Example |
|---------|-------------|---------|
| `title` | Feed title | "My Blog" |
| `link` | Website URL | "https://example.com" |
| `description` | Feed description | "Latest posts" |

### Optional Channel Elements

| Element | Description |
|---------|-------------|
| `language` | Feed language code |
| `pubDate` | Publication date |
| `lastBuildDate` | Last update date |
| `generator` | Software that generated feed |
| `image` | Channel image/logo |

### Required Item Elements

| Element | Description | Mapping |
|---------|-------------|---------|
| `title` | Item title | Post title |
| `link` | Item URL | Post permalink |
| `description` | Item content | Post excerpt or content |

### Optional Item Elements

| Element | Description | Mapping |
|---------|-------------|---------|
| `author` | Author email | Post author |
| `category` | Item categories | Post categories/tags |
| `pubDate` | Publication date | Post published_at |
| `guid` | Unique identifier | Post ID or URL |
| `enclosure` | Media attachment | Featured image |

## Feed Auto-Discovery

RSS auto-discovery uses a `<link>` tag in the HTML head:

```html
<link rel="alternate" type="application/rss+xml"
      title="Blog RSS Feed"
      href="https://example.com/feed">
```

In Blade layout:

```blade
@include('feed::links')
```

Or manually:

```blade
<link rel="alternate" type="application/rss+xml"
      title="{{ config('app.name') }} RSS Feed"
      href="{{ route('feed') }}">
```

## Content Encoding

### Special Characters

RSS requires proper XML encoding for special characters:
- `<` → `&lt;`
- `>` → `&gt;`
- `&` → `&amp;`
- `"` → `&quot;`
- `'` → `&apos;`

The spatie package handles this automatically using CDATA sections for content.

### HTML in Content

Full HTML can be included in description by wrapping in CDATA:

```xml
<description><![CDATA[
  <p>This is <strong>HTML</strong> content.</p>
]]></description>
```

## Implementation Strategy

### Recommended Approach: Hybrid

1. **Main Feed**: Configure in `config/feed.php`
2. **Category/Author Feeds**: Use FeedController for dynamic generation
3. **Auto-discovery**: Use `@include('feed::links')` in layout

### Feed URLs

| Feed Type | URL Pattern | Title Format |
|-----------|-------------|--------------|
| Main | `/feed` | "Blog Name" |
| Category | `/category/{slug}/feed` | "Category Name - Blog Name" |
| Author | `/author/{id}/feed` | "Author Name - Blog Name" |

### Content Strategy

For feed item description:
1. Use excerpt if available (preferred)
2. Truncate content to ~500 characters if no excerpt
3. Include featured image as enclosure

## Testing Considerations

1. **XML Validity**: Validate against RSS 2.0 schema
2. **Feed Reader Compatibility**: Test with Feedly, Inoreader, NetNewsWire
3. **Character Encoding**: Test with special characters and emoji
4. **Content Rendering**: Verify HTML displays correctly in readers
5. **Caching**: Test cache invalidation on new posts

## Performance

- Limit feed to 20-50 most recent items
- Cache feed output (5-15 minutes)
- Eager load relationships (author, categories)

## Dependencies Decision

| Package | Purpose | Recommendation |
|---------|---------|----------------|
| spatie/laravel-feed | RSS/Atom generation | **Install** |

## Risk Assessment

- **Low Risk**: Standard package with wide adoption
- **Compatibility**: Follows RSS 2.0 specification
- **Maintenance**: Actively maintained by Spatie
