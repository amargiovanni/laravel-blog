# Implementation Plan: Related Posts

**Branch**: `011-related-posts` | **Date**: 2025-11-25 | **Spec**: [spec.md](spec.md)

## Summary

Display related posts on single post pages based on shared tags, categories, and recency. Implement relevance scoring algorithm with caching for optimal performance.

## Technical Context

**Language/Version**: PHP 8.4+ with Laravel 12
**Primary Dependencies**: Laravel Cache, Eloquent
**Testing**: Pest 4.x

## Constitution Check

All principles: ✅ PASS

## Project Structure

```text
app/
├── Models/
│   └── Post.php  # Add relatedPosts() method
├── Services/
│   └── RelatedPostsService.php
└── View/Components/
    └── RelatedPosts.php

resources/views/
└── components/
    └── related-posts.blade.php
```

## Implementation Tasks

### Phase 1: Service Implementation

| Task | Description | Acceptance |
|------|-------------|------------|
| T1.1 | Create RelatedPostsService | Service class exists |
| T1.2 | Implement tag-based matching | Posts with shared tags found |
| T1.3 | Implement category-based matching | Posts with shared category found |
| T1.4 | Implement relevance scoring | Score calculation works |
| T1.5 | Implement results ordering | Posts sorted by score descending |

### Phase 2: Caching Layer

| Task | Description | Acceptance |
|------|-------------|------------|
| T2.1 | Add cache for related posts | Results cached |
| T2.2 | Implement cache key generation | Unique key per post |
| T2.3 | Add cache invalidation on post update | Cache clears on tag/category change |
| T2.4 | Set appropriate cache TTL | Cache expires after configured time |

### Phase 3: Model Integration

| Task | Description | Acceptance |
|------|-------------|------------|
| T3.1 | Add relatedPosts() method to Post | Method returns collection |
| T3.2 | Exclude current post from results | Self not in related |
| T3.3 | Exclude unpublished posts | Only published posts |
| T3.4 | Implement configurable limit | Limit parameter works |

### Phase 4: Fallback Logic

| Task | Description | Acceptance |
|------|-------------|------------|
| T4.1 | Implement recent posts fallback | Recent posts fill gaps |
| T4.2 | Handle no matches scenario | Graceful fallback |
| T4.3 | Handle insufficient matches | Fills with recent posts |

### Phase 5: View Component

| Task | Description | Acceptance |
|------|-------------|------------|
| T5.1 | Create RelatedPosts component | Component renders |
| T5.2 | Display thumbnail, title, date | All elements present |
| T5.3 | Link to post page | Links work correctly |
| T5.4 | Handle missing thumbnail | Fallback image shown |
| T5.5 | Responsive grid layout | Works on all devices |

### Phase 6: Frontend Integration

| Task | Description | Acceptance |
|------|-------------|------------|
| T6.1 | Add component to post view | Component appears below content |
| T6.2 | Style related posts section | Matches site design |
| T6.3 | Hide when no related posts | Section hidden gracefully |

### Phase 7: Testing

| Task | Description | Acceptance |
|------|-------------|------------|
| T7.1 | Test tag-based matching | Correct posts returned |
| T7.2 | Test category-based matching | Correct posts returned |
| T7.3 | Test relevance ordering | Higher scores first |
| T7.4 | Test current post exclusion | Self excluded |
| T7.5 | Test unpublished exclusion | Drafts excluded |
| T7.6 | Test fallback behavior | Fallback works |
| T7.7 | Test cache invalidation | Cache updates |
| T7.8 | Test component rendering | UI renders correctly |

## Dependencies

### External Packages

No additional packages required - uses Laravel core features.

### Internal Dependencies

- Post model with tags and categories relationships
- spatie/laravel-tags for tag system
- Public frontend views from 007-public-frontend

## Risk Assessment

| Risk | Probability | Impact | Mitigation |
|------|-------------|--------|------------|
| Performance on large sites | Medium | Medium | Caching strategy |
| Poor relevance quality | Low | Medium | Algorithm tuning |
| Cache staleness | Low | Low | Proper invalidation |

## Artifacts

- [research.md](research.md) - Algorithm analysis and implementation approaches
- [data-model.md](data-model.md) - Relevance scoring and cache structure
- [quickstart.md](quickstart.md) - Quick implementation guide
- [contracts/routes.md](contracts/routes.md) - Component contracts
