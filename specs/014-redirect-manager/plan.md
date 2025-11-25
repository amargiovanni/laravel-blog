# Implementation Plan: Redirect Manager

**Branch**: `014-redirect-manager` | **Date**: 2025-11-25 | **Spec**: [spec.md](spec.md)

## Summary

Implement redirect management system with 301/302 support, admin interface via Filament, automatic redirects on slug changes, hit tracking, and CSV import/export. Uses middleware for high-performance redirect execution.

## Technical Context

**Language/Version**: PHP 8.4+ with Laravel 12
**Primary Dependencies**: Filament 4.x, Laravel Middleware
**Storage**: MySQL/SQLite with caching
**Testing**: Pest 4.x

## Constitution Check

All principles: ✅ PASS

## Project Structure

```text
app/
├── Models/
│   └── Redirect.php
├── Filament/Resources/
│   └── RedirectResource.php
├── Http/Middleware/
│   └── HandleRedirects.php
├── Observers/
│   └── PostObserver.php
└── Services/
    └── RedirectService.php

database/migrations/
└── create_redirects_table.php
```

## Implementation Tasks

### Phase 1: Database & Model

| Task | Description | Acceptance |
|------|-------------|------------|
| T1.1 | Create redirects migration | Table exists with all fields |
| T1.2 | Create Redirect model | Model with validation |
| T1.3 | Add indexes for performance | Source URL indexed |

### Phase 2: Middleware

| Task | Description | Acceptance |
|------|-------------|------------|
| T2.1 | Create HandleRedirects middleware | Middleware registered |
| T2.2 | Implement redirect matching | URLs matched correctly |
| T2.3 | Execute 301/302 redirects | Correct status codes |
| T2.4 | Preserve query strings | Params passed through |
| T2.5 | Update hit counter | Count increments |

### Phase 3: Caching

| Task | Description | Acceptance |
|------|-------------|------------|
| T3.1 | Cache redirect rules | Rules cached |
| T3.2 | Invalidate on CRUD | Cache cleared on changes |
| T3.3 | Optimize for performance | < 10ms redirect time |

### Phase 4: Admin Interface

| Task | Description | Acceptance |
|------|-------------|------------|
| T4.1 | Create RedirectResource | Filament resource exists |
| T4.2 | Implement create form | Source, target, type fields |
| T4.3 | Implement list view | All redirects listed |
| T4.4 | Implement edit/delete | CRUD operations work |
| T4.5 | Show hit count | Statistics visible |

### Phase 5: Validation

| Task | Description | Acceptance |
|------|-------------|------------|
| T5.1 | Prevent redirect loops | Loop detection works |
| T5.2 | Prevent self-redirect | Same URL blocked |
| T5.3 | Warn on existing content | Warning shown |
| T5.4 | Validate URL formats | Invalid URLs rejected |

### Phase 6: Auto-Redirect on Slug Change

| Task | Description | Acceptance |
|------|-------------|------------|
| T6.1 | Create PostObserver | Observer registered |
| T6.2 | Detect slug changes | Changes detected |
| T6.3 | Auto-create 301 redirect | Redirect created |
| T6.4 | Mark as automatic | Source tracked |

### Phase 7: Import/Export

| Task | Description | Acceptance |
|------|-------------|------------|
| T7.1 | Implement CSV export | Download works |
| T7.2 | Implement CSV import | Upload works |
| T7.3 | Handle duplicates | Skipped with message |
| T7.4 | Validate import data | Errors reported |

### Phase 8: Testing

| Task | Description | Acceptance |
|------|-------------|------------|
| T8.1 | Test 301 redirect | Correct status code |
| T8.2 | Test 302 redirect | Correct status code |
| T8.3 | Test query string preservation | Params preserved |
| T8.4 | Test loop prevention | Loops blocked |
| T8.5 | Test auto-redirect creation | Created on slug change |
| T8.6 | Test hit counting | Count increments |
| T8.7 | Test import/export | Round-trip works |
| T8.8 | Test caching | Performance acceptable |

## Dependencies

### External Packages

| Package | Version | Purpose |
|---------|---------|---------|
| filament/filament | ^4.0 | Admin panel |

Uses Laravel built-in: Middleware, Cache, Observers.

### Internal Dependencies

- Post model from 001-blog-engine
- Page model from 003-static-pages

## Risk Assessment

| Risk | Probability | Impact | Mitigation |
|------|-------------|--------|------------|
| Performance overhead | Medium | Medium | Aggressive caching |
| Redirect loops | Low | High | Validation rules |
| Cache staleness | Low | Medium | Event-based invalidation |

## Artifacts

- [research.md](research.md) - Middleware and caching strategies
- [data-model.md](data-model.md) - Redirect schema and validation
- [quickstart.md](quickstart.md) - Quick implementation guide
- [contracts/routes.md](contracts/routes.md) - Route and middleware contracts
