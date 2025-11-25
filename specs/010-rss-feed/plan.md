# Implementation Plan: RSS Feed

**Branch**: `010-rss-feed` | **Date**: 2025-11-25 | **Spec**: [spec.md](spec.md)

## Summary

Implement RSS 2.0 feeds for blog content including main feed, category feeds, and author feeds. Support feed auto-discovery and proper XML generation with post metadata.

## Technical Context

**Language/Version**: PHP 8.4+ with Laravel 12
**Primary Dependencies**: spatie/laravel-feed
**Testing**: Pest 4.x

## Constitution Check

All principles: ✅ PASS

## Project Structure

```text
app/
├── Http/Controllers/
│   └── FeedController.php
└── Models/
    └── Post.php  # Add Feedable interface

config/feed.php  # Feed configuration

routes/web.php  # Feed routes
```

## Implementation Tasks

### Phase 1: Package Setup

| Task | Description | Acceptance |
|------|-------------|------------|
| T1.1 | Install spatie/laravel-feed | Package in composer.json |
| T1.2 | Publish feed config | config/feed.php exists |

### Phase 2: Main Feed Implementation

| Task | Description | Acceptance |
|------|-------------|------------|
| T2.1 | Configure main feed in config | Feed config has main feed entry |
| T2.2 | Implement Feedable on Post model | Post implements toFeedItem() |
| T2.3 | Add feed route at /feed | Route accessible and returns XML |
| T2.4 | Configure feed metadata | Title, description, link present |
| T2.5 | Set feed item limit | Only last 20 posts included |

### Phase 3: Category and Author Feeds

| Task | Description | Acceptance |
|------|-------------|------------|
| T3.1 | Create FeedController | Controller exists |
| T3.2 | Implement category feed route | /category/{slug}/feed works |
| T3.3 | Implement author feed route | /author/{id}/feed works |
| T3.4 | Configure dynamic feed titles | Feed title includes category/author name |

### Phase 4: Feed Content

| Task | Description | Acceptance |
|------|-------------|------------|
| T4.1 | Include post title | All items have title |
| T4.2 | Include post content/excerpt | All items have description |
| T4.3 | Include publication date | All items have pubDate |
| T4.4 | Include author name | All items have author |
| T4.5 | Include categories/tags | Items have category elements |
| T4.6 | Include featured image enclosure | Items with images have enclosure |
| T4.7 | Include permalink | All items have link and guid |

### Phase 5: Auto-Discovery

| Task | Description | Acceptance |
|------|-------------|------------|
| T5.1 | Add RSS link tag to layout | Link tag in HTML head |
| T5.2 | Add category-specific link on category pages | Contextual feed link present |
| T5.3 | Add visible RSS icon/link | RSS link in header/footer |

### Phase 6: Testing

| Task | Description | Acceptance |
|------|-------------|------------|
| T6.1 | Test main feed XML validity | Valid RSS 2.0 output |
| T6.2 | Test post inclusion | Published posts in feed |
| T6.3 | Test draft exclusion | Drafts not in feed |
| T6.4 | Test category feed filtering | Only category posts |
| T6.5 | Test author feed filtering | Only author posts |
| T6.6 | Test special character escaping | XML valid with special chars |
| T6.7 | Test empty feed scenario | Valid empty feed returned |
| T6.8 | Test invalid category 404 | 404 for non-existent category |

## Dependencies

### External Packages

| Package | Version | Purpose |
|---------|---------|---------|
| spatie/laravel-feed | ^4.0 | RSS feed generation |

### Internal Dependencies

- Post model with published scope
- Category model
- User model
- Public frontend routes from 007-public-frontend

## Risk Assessment

| Risk | Probability | Impact | Mitigation |
|------|-------------|--------|------------|
| XML encoding issues | Low | Medium | Use package's encoding |
| Feed reader compatibility | Low | Medium | Test with major readers |
| Performance with many posts | Low | Low | Limit feed items |

## Artifacts

- [research.md](research.md) - Package analysis and RSS specification
- [data-model.md](data-model.md) - Feed structure and content mapping
- [quickstart.md](quickstart.md) - Quick implementation guide
- [contracts/routes.md](contracts/routes.md) - Route specifications
