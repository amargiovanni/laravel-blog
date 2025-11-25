# Implementation Plan: Widgets & Sidebar

**Branch**: `005-widgets-sidebar` | **Date**: 2025-11-25 | **Spec**: [spec.md](spec.md)

## Summary

Implement configurable widget areas (sidebar, footer columns) with drag-and-drop widget management. Includes predefined widgets: Search, Recent Posts, Categories, Tags, Archives, Custom HTML.

## Technical Context

**Language/Version**: PHP 8.4+ with Laravel 12
**Primary Dependencies**: Filament 4.x, Livewire 3
**Storage**: SQLite/MySQL via Eloquent ORM (widget instances stored in DB)
**Testing**: Pest 4.x
**Performance Goals**: Widget render < 50ms each, cached output

## Constitution Check

| Principle | Status | Notes |
|-----------|--------|-------|
| I. Test-First Development | ✅ PASS | Pest tests first |
| II. Laravel Conventions | ✅ PASS | Eloquent, Blade components |
| III. TALL Stack Compliance | ✅ PASS | Livewire widgets |
| IV. Code Quality Gates | ✅ PASS | Pint, Larastan, tests |
| V. Simplicity & YAGNI | ✅ PASS | Simple widget registry |

## Project Structure

```text
app/
├── Models/
│   ├── WidgetArea.php          # Widget area config
│   └── WidgetInstance.php      # Widget placements
├── Widgets/
│   ├── BaseWidget.php          # Abstract base
│   ├── SearchWidget.php
│   ├── RecentPostsWidget.php
│   ├── CategoriesWidget.php
│   ├── TagsWidget.php
│   ├── ArchivesWidget.php
│   └── CustomHtmlWidget.php
├── Filament/
│   └── Pages/
│       └── WidgetManager.php   # Widget management page
└── View/Components/
    └── WidgetArea.php          # Blade component

resources/views/
└── widgets/                    # Widget templates
```
