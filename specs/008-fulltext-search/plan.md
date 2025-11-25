# Implementation Plan: Full-Text Search

**Branch**: `008-fulltext-search` | **Date**: 2025-11-25 | **Spec**: [spec.md](spec.md)

## Summary

Implement full-text search across posts, pages, categories, and tags using Laravel Scout with database driver (or Meilisearch for production). Includes relevance ranking, highlighted excerpts, and optional auto-suggestions.

## Technical Context

**Language/Version**: PHP 8.4+ with Laravel 12
**Primary Dependencies**: Laravel Scout, Database/Meilisearch driver
**Testing**: Pest 4.x
**Performance Goals**: Results < 500ms for 10k posts

## Constitution Check

| Principle | Status | Notes |
|-----------|--------|-------|
| I. Test-First Development | ✅ PASS | |
| II. Laravel Conventions | ✅ PASS | Laravel Scout |
| III. TALL Stack Compliance | ✅ PASS | Livewire search component |
| IV. Code Quality Gates | ✅ PASS | |
| V. Simplicity & YAGNI | ✅ PASS | Start with DB driver |

## Project Structure

```text
app/
├── Http/Controllers/
│   └── SearchController.php
├── Livewire/
│   └── SearchBox.php              # Auto-suggest component
└── Services/
    └── SearchService.php          # Search logic

resources/views/
└── search/
    └── results.blade.php
```
