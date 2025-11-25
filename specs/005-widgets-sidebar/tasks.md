# Tasks – 005-widgets-sidebar

> Generated from spec.md, plan.md, and design artifacts.

---

## Phase 1: Setup

- [ ] [T001] [P1] Create feature branch `005-widgets-sidebar` from main (if not exists)
- [ ] [T002] [P1] Verify PHP 8.4+, Laravel 12, Filament 4.x, Livewire 3 dependencies

---

## Phase 2: Foundational

- [ ] [T003] [P1] Create migration for `widget_instances` table with fields: id, area, widget_type, title, settings (json), sort_order, timestamps → `database/migrations/xxxx_create_widget_instances_table.php`
- [ ] [T004] [P1] Create WidgetInstance model with fillable, casts (settings as array) → `app/Models/WidgetInstance.php`
- [ ] [T005] [P1] Create widgets config file with areas definition → `config/widgets.php`
- [ ] [T006] [P1] Create BaseWidget abstract class with render, settings, name methods → `app/Widgets/BaseWidget.php`
- [ ] [T007] [P1] Create WidgetInstanceFactory → `database/factories/WidgetInstanceFactory.php`
- [ ] [T008] [P1] Create WidgetSeeder for development data → `database/seeders/WidgetSeeder.php`
- [ ] [T009] [P1] Run migration and verify database schema

---

## Phase 3: US1 – Add Widgets to Sidebar (P1) 🎯 MVP

- [ ] [T010] [P1] [US1] Create WidgetManager Filament custom page → `app/Filament/Pages/WidgetManager.php`
- [ ] [T011] [P1] [US1] Implement widget areas display in WidgetManager → `app/Filament/Pages/WidgetManager.php`
- [ ] [T012] [P1] [US1] Implement available widgets panel → `app/Filament/Pages/WidgetManager.php`
- [ ] [T013] [P1] [US1] Create SearchWidget with search form rendering → `app/Widgets/SearchWidget.php`
- [ ] [T014] [P1] [US1] Create search widget blade view → `resources/views/widgets/search.blade.php`
- [ ] [T015] [P1] [US1] Create RecentPostsWidget with configurable post count → `app/Widgets/RecentPostsWidget.php`
- [ ] [T016] [P1] [US1] Create recent posts widget blade view → `resources/views/widgets/recent-posts.blade.php`
- [ ] [T017] [P1] [US1] Create CategoriesWidget showing categories with counts → `app/Widgets/CategoriesWidget.php`
- [ ] [T018] [P1] [US1] Create categories widget blade view → `resources/views/widgets/categories.blade.php`
- [ ] [T019] [P1] [US1] Create WidgetArea Blade component for frontend rendering → `app/View/Components/WidgetArea.php`
- [ ] [T020] [P1] [US1] Create widget-area blade view → `resources/views/components/widget-area.blade.php`
- [ ] [T021] [P1] [US1] Write feature tests for widget CRUD operations → `tests/Feature/WidgetTest.php`

---

## Phase 4: US2 – Configure Widget Settings (P1) 🎯 MVP

- [ ] [T022] [P1] [US2] Implement widget settings form in WidgetManager → `app/Filament/Pages/WidgetManager.php`
- [ ] [T023] [P1] [US2] Add getSettingsFields() abstract method to BaseWidget → `app/Widgets/BaseWidget.php`
- [ ] [T024] [P1] [US2] Implement RecentPostsWidget settings (number of posts: 5, 10, 15) → `app/Widgets/RecentPostsWidget.php`
- [ ] [T025] [P1] [US2] Add title field to all widget settings forms → `app/Widgets/BaseWidget.php`
- [ ] [T026] [P1] [US2] Implement settings persistence in WidgetInstance model → `app/Models/WidgetInstance.php`
- [ ] [T027] [P1] [US2] Pass settings to widget render method → `app/View/Components/WidgetArea.php`
- [ ] [T028] [P1] [US2] Write tests for widget configuration persistence → `tests/Feature/WidgetSettingsTest.php`

---

## Phase 5: US3 – Reorder Widgets with Drag-and-Drop (P2)

- [ ] [T029] [P2] [US3] Implement drag-and-drop UI in WidgetManager with Livewire/Alpine → `app/Filament/Pages/WidgetManager.php`
- [ ] [T030] [P2] [US3] Create reorder action to update sort_order → `app/Filament/Pages/WidgetManager.php`
- [ ] [T031] [P2] [US3] Implement move widget between areas functionality → `app/Filament/Pages/WidgetManager.php`
- [ ] [T032] [P2] [US3] Create widget-manager blade view with drag-drop JS → `resources/views/filament/pages/widget-manager.blade.php`
- [ ] [T033] [P2] [US3] Write tests for widget reordering → `tests/Feature/WidgetReorderTest.php`
- [ ] [T034] [P2] [US3] Write browser tests for drag-drop interactions → `tests/Browser/WidgetDragDropTest.php`

---

## Phase 6: US4 – Manage Multiple Widget Areas (P2)

- [ ] [T035] [P2] [US4] Define all widget areas in config: primary_sidebar, footer_1, footer_2, footer_3 → `config/widgets.php`
- [ ] [T036] [P2] [US4] Display all widget areas in WidgetManager UI → `app/Filament/Pages/WidgetManager.php`
- [ ] [T037] [P2] [US4] Update WidgetArea component to accept area parameter → `app/View/Components/WidgetArea.php`
- [ ] [T038] [P2] [US4] Implement graceful fallback for empty widget areas → `app/View/Components/WidgetArea.php`
- [ ] [T039] [P2] [US4] Write tests for multiple widget areas → `tests/Feature/WidgetAreaTest.php`

---

## Phase 7: US5 – Use Available Widget Types (P2)

- [ ] [T040] [P2] [US5] Create TagsWidget with tag cloud rendering → `app/Widgets/TagsWidget.php`
- [ ] [T041] [P2] [US5] Create tags widget blade view with size variations → `resources/views/widgets/tags.blade.php`
- [ ] [T042] [P2] [US5] Create ArchivesWidget with monthly/yearly grouping → `app/Widgets/ArchivesWidget.php`
- [ ] [T043] [P2] [US5] Create archives widget blade view → `resources/views/widgets/archives.blade.php`
- [ ] [T044] [P2] [US5] Create CustomHtmlWidget with HTML content field → `app/Widgets/CustomHtmlWidget.php`
- [ ] [T045] [P2] [US5] Create custom HTML widget blade view → `resources/views/widgets/custom-html.blade.php`
- [ ] [T046] [P2] [US5] Implement HTML sanitization in CustomHtmlWidget for XSS protection → `app/Widgets/CustomHtmlWidget.php`
- [ ] [T047] [P2] [US5] Create WidgetRegistry service to discover available widgets → `app/Services/WidgetRegistry.php`
- [ ] [T048] [P2] [US5] Write tests for each widget type functionality → `tests/Feature/WidgetTypesTest.php`
- [ ] [T049] [P2] [US5] Write security tests for CustomHtmlWidget sanitization → `tests/Feature/WidgetSecurityTest.php`

---

## Phase 8: Polish & Cross-Cutting Concerns

- [ ] [T050] [P2] Implement widget output caching in WidgetArea component → `app/View/Components/WidgetArea.php`
- [ ] [T051] [P2] Add cache invalidation when widget settings change → `app/Models/WidgetInstance.php`
- [ ] [T052] [P2] Create WidgetPolicy with viewAny, create, update, delete methods → `app/Policies/WidgetPolicy.php`
- [ ] [T053] [P2] Register WidgetPolicy and protect WidgetManager page → `app/Providers/AuthServiceProvider.php`
- [ ] [T054] [P3] Handle empty states (no posts, no categories) in widgets → `app/Widgets/*.php`
- [ ] [T055] [P3] Add delete widget action in WidgetManager → `app/Filament/Pages/WidgetManager.php`
- [ ] [T056] [P3] Implement mobile responsive widget areas → `resources/views/components/widget-area.blade.php`
- [ ] [T057] [P1] Run full test suite and fix any failures
- [ ] [T058] [P1] Run `vendor/bin/pint --dirty` to fix code style
- [ ] [T059] [P1] Update README or documentation if needed
- [ ] [T060] [P1] Create PR and merge to main branch

---

## Summary

| Phase | Tasks | Priority Focus |
|-------|-------|----------------|
| Setup | 2 | P1 |
| Foundational | 7 | P1 |
| US1 – Add Widgets | 12 | P1 🎯 MVP |
| US2 – Configure Settings | 7 | P1 🎯 MVP |
| US3 – Drag-and-Drop | 6 | P2 |
| US4 – Multiple Areas | 5 | P2 |
| US5 – Widget Types | 10 | P2 |
| Polish | 11 | Mixed |

**Total Tasks: 60**
