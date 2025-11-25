# Implementation Plan: Static Pages

**Branch**: `003-static-pages` | **Date**: 2025-11-25 | **Spec**: [spec.md](spec.md)
**Input**: Feature specification from `/specs/003-static-pages/spec.md`

## Summary

Implement a static pages system for the Laravel blog, enabling creation of non-blog content like "About Us", "Privacy Policy", and "Contact" pages. The feature includes hierarchical page organization (parent/child), multiple templates, SEO metadata, draft/publish workflow, and Filament admin integration.

## Technical Context

**Language/Version**: PHP 8.4+ with Laravel 12
**Primary Dependencies**: Filament 4.x, Livewire 3, Volt, Flux UI
**Storage**: SQLite (dev) / MySQL/PostgreSQL (prod) via Eloquent ORM
**Testing**: Pest 4.x with Feature and Browser tests
**Target Platform**: Web application (Linux server)
**Project Type**: Web application with Filament admin panel
**Performance Goals**: Page load < 2 seconds, admin operations < 2 seconds
**Constraints**: Must follow existing Post model patterns, SEO metadata required
**Scale/Scope**: Supports hundreds of pages with 3-level hierarchy

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

| Principle | Status | Notes |
|-----------|--------|-------|
| I. Test-First Development | ✅ PASS | Will write Pest tests before implementation |
| II. Laravel Conventions | ✅ PASS | Using Eloquent, Form Requests, artisan commands |
| III. TALL Stack Compliance | ✅ PASS | Filament 4.x for admin, follows existing patterns |
| IV. Code Quality Gates | ✅ PASS | Will run Pint, Larastan, tests before completion |
| V. Simplicity & YAGNI | ✅ PASS | Reuses existing patterns from Post model |

## Project Structure

### Documentation (this feature)

```text
specs/003-static-pages/
├── plan.md              # This file
├── research.md          # Phase 0 output
├── data-model.md        # Phase 1 output
├── quickstart.md        # Phase 1 output
├── contracts/           # Phase 1 output
└── tasks.md             # Phase 2 output (via /speckit.tasks)
```

### Source Code (repository root)

```text
app/
├── Models/
│   └── Page.php                    # Page Eloquent model
├── Filament/
│   └── Resources/
│       └── PageResource.php        # Filament admin resource
│       └── PageResource/
│           └── Pages/              # CRUD pages
├── Http/
│   └── Controllers/
│       └── PageController.php      # Frontend controller
├── Policies/
│   └── PagePolicy.php              # Authorization policy
└── Console/
    └── Commands/
        └── PublishScheduledPages.php  # Scheduled publishing

database/
└── migrations/
    └── xxxx_create_pages_table.php

resources/
└── views/
    └── pages/
        ├── show.blade.php          # Default template
        ├── full-width.blade.php    # Full-width template
        └── with-sidebar.blade.php  # Sidebar template

routes/
└── web.php                         # Page routes

tests/
├── Feature/
│   └── PageTest.php
└── Unit/
    └── Models/
        └── PageTest.php
```

**Structure Decision**: Follows existing Laravel/Filament structure with Post model as reference. Pages use same patterns for consistency.
