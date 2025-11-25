# Implementation Plan: XML Sitemap

**Branch**: `009-xml-sitemap` | **Date**: 2025-11-25 | **Spec**: [spec.md](spec.md)

## Summary

Generate XML sitemaps for SEO including posts, pages, categories, tags, and authors. Support sitemap index for large sites, automatic updates on content changes.

## Technical Context

**Language/Version**: PHP 8.4+ with Laravel 12
**Primary Dependencies**: spatie/laravel-sitemap
**Testing**: Pest 4.x

## Constitution Check

All principles: ✅ PASS

## Project Structure

```text
app/
├── Http/Controllers/
│   └── SitemapController.php
└── Console/Commands/
    └── GenerateSitemap.php

routes/web.php  # /sitemap.xml route
```

## Implementation Tasks

### Phase 1: Package Setup

| Task | Description | Acceptance |
|------|-------------|------------|
| T1.1 | Install spatie/laravel-sitemap | Package in composer.json |
| T1.2 | Create SitemapController | Controller exists and compiles |

### Phase 2: Core Sitemap Generation

| Task | Description | Acceptance |
|------|-------------|------------|
| T2.1 | Implement homepage URL addition | Homepage in sitemap with priority 1.0 |
| T2.2 | Implement posts URL addition | All published posts in sitemap |
| T2.3 | Implement pages URL addition | All published pages in sitemap |
| T2.4 | Implement categories URL addition | Categories with posts in sitemap |
| T2.5 | Implement tags URL addition | Tags with posts in sitemap |
| T2.6 | Implement authors URL addition | Authors with posts in sitemap |
| T2.7 | Add lastmod metadata | All entries have lastmod dates |
| T2.8 | Add changefreq metadata | All entries have changefreq |
| T2.9 | Add priority metadata | All entries have priority values |

### Phase 3: Route and Access

| Task | Description | Acceptance |
|------|-------------|------------|
| T3.1 | Register /sitemap.xml route | Route accessible publicly |
| T3.2 | Implement XML response | Valid XML returned |
| T3.3 | Update robots.txt with sitemap URL | Sitemap referenced in robots.txt |

### Phase 4: Image Sitemap Support

| Task | Description | Acceptance |
|------|-------------|------------|
| T4.1 | Add featured images to post URLs | Images included in sitemap entries |
| T4.2 | Proper image URL encoding | All image URLs valid |

### Phase 5: Artisan Command (Caching)

| Task | Description | Acceptance |
|------|-------------|------------|
| T5.1 | Create GenerateSitemap command | Command runs successfully |
| T5.2 | Write sitemap to public folder | File created at public/sitemap.xml |
| T5.3 | Add command output feedback | User sees generation stats |

### Phase 6: Testing

| Task | Description | Acceptance |
|------|-------------|------------|
| T6.1 | Test sitemap XML validity | Valid XML structure verified |
| T6.2 | Test post inclusion | Published posts appear |
| T6.3 | Test draft exclusion | Drafts do not appear |
| T6.4 | Test page inclusion | Published pages appear |
| T6.5 | Test category inclusion | Categories with posts appear |
| T6.6 | Test tag inclusion | Tags with posts appear |
| T6.7 | Test metadata presence | lastmod, changefreq, priority present |
| T6.8 | Test special character encoding | URLs properly encoded |

## Dependencies

### External Packages

| Package | Version | Purpose |
|---------|---------|---------|
| spatie/laravel-sitemap | ^7.0 | Sitemap generation |

### Internal Dependencies

- Post model with `published` scope
- Page model from 003-static-pages
- Category model
- Tag model (spatie/laravel-tags)
- User model
- Public frontend routes from 007-public-frontend

## Risk Assessment

| Risk | Probability | Impact | Mitigation |
|------|-------------|--------|------------|
| Large site performance | Low | Medium | Use static file generation |
| URL encoding issues | Low | Low | Spatie package handles this |
| Missing content types | Low | Low | Comprehensive testing |

## Artifacts

- [research.md](research.md) - Package analysis and implementation strategy
- [data-model.md](data-model.md) - Source models and URL structure
- [quickstart.md](quickstart.md) - Quick implementation guide
- [contracts/routes.md](contracts/routes.md) - Route specifications
