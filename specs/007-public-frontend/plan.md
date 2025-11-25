# Implementation Plan: Public Frontend

**Branch**: `007-public-frontend` | **Date**: 2025-11-25 | **Spec**: [spec.md](spec.md)

## Summary

Implement the public-facing blog templates: homepage, single post, single page, category/tag/author archives, date archives, search results, and 404 page. Responsive design with TailwindCSS, dark mode support, and SEO metadata.

## Technical Context

**Language/Version**: PHP 8.4+ with Laravel 12
**Primary Dependencies**: Livewire 3, Volt, TailwindCSS 4, Flux UI
**Storage**: Eloquent ORM
**Testing**: Pest 4.x with Browser tests
**Performance Goals**: Page load < 2s, Core Web Vitals pass
**Constraints**: Responsive 320px-2560px, dark mode support

## Constitution Check

| Principle | Status | Notes |
|-----------|--------|-------|
| I. Test-First Development | ✅ PASS | Browser tests for each template |
| II. Laravel Conventions | ✅ PASS | Controllers, Blade, named routes |
| III. TALL Stack Compliance | ✅ PASS | Livewire/Volt for interactive parts |
| IV. Code Quality Gates | ✅ PASS | Pint, Larastan, tests |
| V. Simplicity & YAGNI | ✅ PASS | Simple Blade templates |

## Dependencies

### Internal Feature Dependencies

| Feature | Dependency Type | Description |
|---------|----------------|-------------|
| 001-blog-engine | Required | Post, Category, Tag, User models |
| 003-static-pages | Required | Page model for static pages |
| 004-menu-builder | Required | Navigation menu rendering (FR-015) |
| 005-widgets-sidebar | Required | Sidebar widget areas (FR-016) |
| 008-fulltext-search | Optional | Search functionality (SearchController, SearchService) |
| 015-comments-system | Required | Comments section on posts (FR-005) |

### Implementation Notes

- **Search**: Do NOT create SearchController in this feature. Use the one from 008-fulltext-search.
- **Menu**: Integrate `<x-navigation-menu>` component from 004-menu-builder
- **Widgets**: Use `<x-widget-area location="sidebar">` from 005-widgets-sidebar
- **Comments**: Include `<livewire:comment-list>` and `<livewire:comment-form>` from 015-comments-system

## Project Structure

```text
app/Http/Controllers/
├── HomeController.php
├── PostController.php
├── CategoryController.php
├── TagController.php
├── AuthorController.php
└── ArchiveController.php

resources/views/
├── layouts/
│   └── blog.blade.php           # Main layout
├── home.blade.php
├── posts/
│   ├── index.blade.php          # Post list
│   └── show.blade.php           # Single post
├── pages/
│   └── show.blade.php           # Single page
├── categories/
│   └── show.blade.php           # Category archive
├── tags/
│   └── show.blade.php           # Tag archive
├── authors/
│   └── show.blade.php           # Author page
├── archives/
│   └── show.blade.php           # Date archive
├── search/
│   └── results.blade.php        # Search results
├── components/
│   ├── post-card.blade.php
│   ├── pagination.blade.php
│   ├── sidebar.blade.php
│   └── seo-meta.blade.php
└── errors/
    └── 404.blade.php
```
