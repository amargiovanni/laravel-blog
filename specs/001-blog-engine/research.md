# Research: Laravel Blog Engine

**Feature**: 001-blog-engine
**Date**: 2025-11-25
**Status**: Complete

## Research Summary

This document consolidates technology decisions and best practices research for the Laravel Blog Engine implementation.

---

## 1. Rich Text Editor

**Decision**: Use Filament's built-in RichEditor component for admin, TipTap for frontend if needed

**Rationale**:
- Filament v4 includes a powerful RichEditor based on TipTap
- No additional dependencies needed for admin panel
- Consistent with TALL stack (Livewire-compatible)
- Supports image uploads, links, tables, code blocks

**Alternatives Considered**:
- CKEditor 5: Heavy, licensing concerns
- Quill: Less actively maintained
- Trix: Limited features compared to TipTap

---

## 2. Image Processing & Optimization

**Decision**: Use Intervention Image v3 with Laravel's built-in storage

**Rationale**:
- Intervention Image is the de-facto standard for Laravel
- Supports GD and Imagick drivers
- Easy thumbnail generation and optimization
- Works with any storage driver (local, S3, etc.)

**Implementation Notes**:
- Generate sizes: thumbnail (150x150), medium (600x400), large (1200x800)
- WebP conversion for modern browsers with fallback
- Lazy loading with `loading="lazy"` attribute
- Store original + generated sizes

**Alternatives Considered**:
- Spatie Media Library: Excellent but adds complexity for this scope
- Cloudinary: External dependency, cost considerations

---

## 3. Roles & Permissions

**Decision**: Use Spatie Laravel-Permission v6

**Rationale**:
- Industry standard for Laravel RBAC
- Excellent Filament integration (filament-spatie-roles-permissions)
- Supports both roles and direct permissions
- Blade directives (@role, @can, @permission)

**Role Structure**:
```
admin       - Full access to everything
editor      - Can manage all posts, comments, categories, tags
author      - Can manage own posts, view others
subscriber  - Can comment (when logged in), manage profile
```

**Alternatives Considered**:
- Laravel Gates only: Insufficient for complex role hierarchies
- Bouncer: Similar features but less Filament integration
- Custom implementation: Unnecessary complexity

---

## 4. Activity Logging

**Decision**: Use Spatie Laravel-Activitylog v4

**Rationale**:
- Automatic model event logging
- Stores before/after states
- Customizable log names and descriptions
- Built-in cleanup command for retention

**Implementation Notes**:
- Log post CRUD, comment moderation, user management
- 90-day retention with `activitylog:clean` scheduled command
- Custom Filament page for activity log viewing

**Alternatives Considered**:
- Custom logging: Too much effort for same result
- Laravel Auditing: Similar but Spatie is more Laravel-native

---

## 5. Full-Text Search

**Decision**: MySQL/PostgreSQL native full-text search initially

**Rationale**:
- No external dependencies
- Sufficient for <50k posts
- Laravel Scout compatible for future upgrade
- Works with MySQL FULLTEXT indexes or PostgreSQL tsvector

**Implementation Notes**:
- Add FULLTEXT index on posts (title, content, excerpt)
- Use `MATCH AGAINST` for MySQL or `to_tsvector` for PostgreSQL
- Wrap in SearchService for easy Scout migration later

**Alternatives Considered**:
- Meilisearch: Excellent but adds infrastructure complexity
- Algolia: Cost and external dependency
- Elasticsearch: Overkill for initial scope

**Future Enhancement**: Add Laravel Scout + Meilisearch when scaling needed

---

## 6. Comment Spam Protection

**Decision**: Multi-layer approach without external services

**Rationale**:
- No API keys or external dependencies
- Effective against most automated spam
- Privacy-friendly (no data sent to third parties)

**Implementation Layers**:
1. **Honeypot field**: Hidden field that bots fill, humans ignore
2. **Rate limiting**: Max 3 comments per minute per IP
3. **Time-based validation**: Form must be open >3 seconds
4. **Link limits**: Max 2 URLs per comment
5. **Blocked words list**: Configurable spam words

**Alternatives Considered**:
- Akismet: Requires API key, external dependency
- reCAPTCHA: User friction, Google dependency
- hCaptcha: Better privacy but still external

---

## 7. Dark Mode Implementation

**Decision**: CSS-based with localStorage preference and system detection

**Rationale**:
- No server roundtrip for theme switching
- Instant toggle (<200ms requirement)
- Respects `prefers-color-scheme` media query
- TailwindCSS v4 dark mode utilities

**Implementation Notes**:
- Use `dark:` prefix in TailwindCSS
- Alpine.js component for toggle with `$persist`
- Set `class="dark"` on `<html>` element
- Check localStorage first, then system preference

```javascript
// Alpine.js theme component
Alpine.data('theme', () => ({
    dark: Alpine.$persist(
        window.matchMedia('(prefers-color-scheme: dark)').matches
    ).as('darkMode'),
    toggle() { this.dark = !this.dark }
}))
```

---

## 8. RSS/Atom Feeds

**Decision**: Use spatie/laravel-feed

**Rationale**:
- Laravel-native feed generation
- Supports RSS 2.0 and Atom
- Automatic route registration
- Category-specific feeds supported

**Implementation Notes**:
- Main feed at `/feed`
- Category feeds at `/category/{slug}/feed`
- Include: title, excerpt, author, published_at, featured image

**Alternatives Considered**:
- Manual XML generation: More work, error-prone
- SimplePie: For consuming, not generating feeds

---

## 9. SEO Implementation

**Decision**: Custom SEO traits + Spatie Laravel-Sitemap

**Rationale**:
- Full control over meta tags
- No heavy package dependencies for basic SEO
- Spatie Sitemap for automatic sitemap generation

**Implementation Notes**:
- `HasSeoMeta` trait for Post model
- Fields: meta_title, meta_description, focus_keyword, og_image
- Auto-generate from title/excerpt if not set
- Sitemap regenerated via scheduled command

**Meta Tags**:
```html
<title>{{ $post->seo_title }}</title>
<meta name="description" content="{{ $post->seo_description }}">
<meta property="og:title" content="{{ $post->seo_title }}">
<meta property="og:description" content="{{ $post->seo_description }}">
<meta property="og:image" content="{{ $post->og_image_url }}">
<meta name="twitter:card" content="summary_large_image">
<link rel="canonical" href="{{ $post->canonical_url }}">
```

---

## 10. Theme System

**Decision**: View namespace-based themes with config setting

**Rationale**:
- Simple implementation using Laravel's view system
- No package needed
- Admin can switch via settings
- Theme-specific assets supported

**Implementation Notes**:
- Themes stored in `resources/views/themes/{theme-name}/`
- Active theme set in `config/blog.php` or database settings
- ThemeServiceProvider registers active theme views
- Fallback to default theme for missing views

**Directory Structure**:
```
resources/views/themes/
├── default/
│   ├── layouts/
│   ├── posts/
│   └── components/
└── modern/
    ├── layouts/
    ├── posts/
    └── components/
```

---

## 11. Hierarchical Categories

**Decision**: Self-referencing model with `parent_id`

**Rationale**:
- Simple, Laravel-native approach
- Works well with Eloquent relationships
- No package needed for basic hierarchy

**Implementation Notes**:
- `parent_id` nullable foreign key
- `children()` and `parent()` relationships
- Recursive query for nested display
- Consider `kalnoy/nestedset` if performance issues arise

**Model Methods**:
```php
public function parent(): BelongsTo
public function children(): HasMany
public function ancestors(): Collection
public function descendants(): Collection
```

---

## 12. Scheduled Post Publishing

**Decision**: Laravel scheduler with database query

**Rationale**:
- Built-in Laravel feature
- Simple cron-based approach
- Handles server downtime gracefully

**Implementation Notes**:
- Posts have `status` enum: draft, scheduled, published
- Posts have `published_at` timestamp (nullable)
- Scheduler runs every minute: `Post::publishScheduled()`
- Query: `where('status', 'scheduled')->where('published_at', '<=', now())`

---

## Package Summary

| Package | Version | Purpose |
|---------|---------|---------|
| spatie/laravel-permission | ^6.0 | Roles & permissions |
| spatie/laravel-activitylog | ^4.0 | Activity logging |
| spatie/laravel-sitemap | ^7.0 | Sitemap generation |
| spatie/laravel-feed | ^4.0 | RSS/Atom feeds |
| intervention/image | ^3.0 | Image processing |

---

## Decisions Not Requiring Research

The following were pre-determined by the project constitution:

- **Framework**: Laravel 12 (constitution requirement)
- **Frontend**: Livewire 3 + Volt (constitution requirement)
- **Admin Panel**: Filament v4 (constitution requirement)
- **CSS**: TailwindCSS v4 (constitution requirement)
- **JS**: Alpine.js v3 (constitution requirement)
- **UI Components**: Flux UI Free (constitution requirement)
- **Testing**: Pest v4 (constitution requirement)
- **Code Style**: Pint + Larastan (constitution requirement)
