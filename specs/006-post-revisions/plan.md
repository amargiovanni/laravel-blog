# Implementation Plan: Post Revisions

**Branch**: `006-post-revisions` | **Date**: 2025-11-25 | **Spec**: [spec.md](spec.md)

## Summary

Implement revision history for posts and pages with diff comparison, restore functionality, and auto-save. Each save creates a revision snapshot with full content, metadata, author, and timestamp.

## Technical Context

**Language/Version**: PHP 8.4+ with Laravel 12
**Primary Dependencies**: Filament 4.x, jfcherng/php-diff for comparison
**Storage**: SQLite/MySQL via Eloquent (revisions table)
**Testing**: Pest 4.x
**Performance Goals**: Comparison < 2s, restore < 3s

## Constitution Check

| Principle | Status | Notes |
|-----------|--------|-------|
| I. Test-First Development | ✅ PASS | Pest tests first |
| II. Laravel Conventions | ✅ PASS | Eloquent, model events |
| III. TALL Stack Compliance | ✅ PASS | Livewire for diff UI |
| IV. Code Quality Gates | ✅ PASS | Pint, Larastan, tests |
| V. Simplicity & YAGNI | ✅ PASS | Simple snapshot approach |

## Project Structure

```text
app/
├── Models/
│   └── Revision.php              # Revision model
├── Observers/
│   └── RevisionObserver.php      # Auto-create on save
├── Services/
│   └── RevisionService.php       # Diff, restore logic
└── Livewire/
    └── RevisionPanel.php         # Filament integration

database/migrations/
└── xxxx_create_revisions_table.php
```
