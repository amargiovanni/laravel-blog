# Tasks – 004-menu-builder

> Generated from spec.md, plan.md, and design artifacts.

---

## Phase 1: Setup

- [ ] [T001] [P1] Create feature branch `004-menu-builder` from main (if not exists)
- [ ] [T002] [P1] Verify PHP 8.4+, Laravel 12, Filament 4.x, Alpine.js dependencies

---

## Phase 2: Foundational

- [ ] [T003] [P1] Create migration for `menus` table with fields: id, name, location (enum: header,footer,mobile,none), timestamps → `database/migrations/xxxx_create_menus_table.php`
- [ ] [T004] [P1] Create migration for `menu_items` table with fields: id, menu_id, parent_id, label, linkable_type, linkable_id, url, target (enum: _self,_blank), css_class, sort_order, timestamps → `database/migrations/xxxx_create_menu_items_table.php`
- [ ] [T005] [P1] Create Menu model with fillable, casts, and items relationship → `app/Models/Menu.php`
- [ ] [T006] [P1] Create MenuItem model with fillable, casts, relationships (menu, parent, children, linkable) → `app/Models/MenuItem.php`
- [ ] [T007] [P1] Create MenuFactory → `database/factories/MenuFactory.php`
- [ ] [T008] [P1] Create MenuItemFactory → `database/factories/MenuItemFactory.php`
- [ ] [T009] [P1] Create MenuSeeder for development data → `database/seeders/MenuSeeder.php`
- [ ] [T010] [P1] Run migrations and verify database schema

---

## Phase 3: US1 – Create and Configure a Navigation Menu (P1) 🎯 MVP

- [ ] [T011] [P1] [US1] Create MenuResource with basic CRUD operations → `app/Filament/Resources/MenuResource.php`
- [ ] [T012] [P1] [US1] Implement form schema with name and location (select) fields → `app/Filament/Resources/MenuResource.php`
- [ ] [T013] [P1] [US1] Add menu items repeater/relation manager to MenuResource → `app/Filament/Resources/MenuResource.php`
- [ ] [T014] [P1] [US1] Implement "Add Page" action to add published pages as menu items → `app/Filament/Resources/MenuResource.php`
- [ ] [T015] [P1] [US1] Implement "Add Category" action to add categories as menu items → `app/Filament/Resources/MenuResource.php`
- [ ] [T016] [P1] [US1] Implement "Add Custom Link" action with URL and label fields → `app/Filament/Resources/MenuResource.php`
- [ ] [T017] [P1] [US1] Create Navigation Blade component for frontend rendering → `app/View/Components/Navigation.php`
- [ ] [T018] [P1] [US1] Create navigation blade view with menu items loop → `resources/views/components/navigation.blade.php`
- [ ] [T019] [P1] [US1] Create MenuService for menu retrieval and caching → `app/Services/MenuService.php`
- [ ] [T020] [P1] [US1] Write feature tests for menu CRUD operations → `tests/Feature/MenuTest.php`
- [ ] [T021] [P1] [US1] Write unit tests for Menu and MenuItem models → `tests/Unit/Models/MenuTest.php`

---

## Phase 4: US2 – Organize Menu Items with Drag-and-Drop (P1) 🎯 MVP

- [ ] [T022] [P1] [US2] Install SortableJS or use Alpine.js drag-drop capabilities → `package.json`
- [ ] [T023] [P1] [US2] Create custom Filament page/action for drag-drop menu editor → `app/Filament/Resources/MenuResource/Pages/EditMenu.php`
- [ ] [T024] [P1] [US2] Implement drag-and-drop reordering UI with Alpine.js → `resources/views/filament/resources/menu-resource/pages/edit-menu.blade.php`
- [ ] [T025] [P1] [US2] Create reorder endpoint for AJAX updates → `app/Filament/Resources/MenuResource.php`
- [ ] [T026] [P1] [US2] Implement nested item support (max 3 levels) in drag-drop UI → `resources/views/filament/resources/menu-resource/pages/edit-menu.blade.php`
- [ ] [T027] [P1] [US2] Add nesting depth validation (max 3 levels) → `app/Models/MenuItem.php`
- [ ] [T028] [P1] [US2] Implement dropdown rendering for nested items on frontend → `resources/views/components/navigation.blade.php`
- [ ] [T029] [P1] [US2] Write tests for drag-drop reordering functionality → `tests/Feature/MenuReorderTest.php`
- [ ] [T030] [P1] [US2] Write browser tests for drag-drop UI interactions → `tests/Browser/MenuDragDropTest.php`

---

## Phase 5: US3 – Add Different Types of Menu Items (P2)

- [ ] [T031] [P2] [US3] Create "Pages" panel/action with searchable published pages list → `app/Filament/Resources/MenuResource.php`
- [ ] [T032] [P2] [US3] Create "Posts" panel/action with searchable published posts list → `app/Filament/Resources/MenuResource.php`
- [ ] [T033] [P2] [US3] Create "Categories" panel/action with all categories list → `app/Filament/Resources/MenuResource.php`
- [ ] [T034] [P2] [US3] Create "Tags" panel/action with all tags list → `app/Filament/Resources/MenuResource.php`
- [ ] [T035] [P2] [US3] Implement polymorphic linkable relationship resolution for URLs → `app/Models/MenuItem.php`
- [ ] [T036] [P2] [US3] Write tests for each item type (page, post, category, tag, custom) → `tests/Feature/MenuItemTypesTest.php`

---

## Phase 6: US4 – Manage Multiple Menu Locations (P2)

- [ ] [T037] [P2] [US4] Define menu locations enum (header, footer, mobile, none) → `app/Enums/MenuLocation.php`
- [ ] [T038] [P2] [US4] Implement unique location constraint (one menu per location) → `app/Models/Menu.php`
- [ ] [T039] [P2] [US4] Update Navigation component to accept location parameter → `app/View/Components/Navigation.php`
- [ ] [T040] [P2] [US4] Implement graceful fallback when no menu assigned to location → `app/View/Components/Navigation.php`
- [ ] [T041] [P2] [US4] Cache menu by location for performance → `app/Services/MenuService.php`
- [ ] [T042] [P2] [US4] Write tests for multiple menu locations → `tests/Feature/MenuLocationTest.php`

---

## Phase 7: US5 – Edit Menu Item Properties (P3)

- [ ] [T043] [P3] [US5] Add label edit field (override linked content title) → `app/Filament/Resources/MenuResource.php`
- [ ] [T044] [P3] [US5] Add title attribute field for accessibility → `app/Filament/Resources/MenuResource.php`
- [ ] [T045] [P3] [US5] Add CSS class field for custom styling → `app/Filament/Resources/MenuResource.php`
- [ ] [T046] [P3] [US5] Add "Open in new tab" toggle (target="_blank") → `app/Filament/Resources/MenuResource.php`
- [ ] [T047] [P3] [US5] Implement custom label rendering (fallback to linked content title) → `app/Models/MenuItem.php`
- [ ] [T048] [P3] [US5] Apply target and CSS class in navigation view → `resources/views/components/navigation.blade.php`
- [ ] [T049] [P3] [US5] Write tests for menu item property customization → `tests/Feature/MenuItemPropertiesTest.php`

---

## Phase 8: Polish & Cross-Cutting Concerns

- [ ] [T050] [P2] Implement menu item URL auto-update when linked content slug changes → `app/Observers/MenuItemObserver.php`
- [ ] [T051] [P2] Add warning when linked content is deleted (orphan detection) → `app/Observers/PageObserver.php`
- [ ] [T052] [P2] Create MenuPolicy with viewAny, view, create, update, delete methods → `app/Policies/MenuPolicy.php`
- [ ] [T053] [P2] Register MenuPolicy in AuthServiceProvider → `app/Providers/AuthServiceProvider.php`
- [ ] [T054] [P3] Add cache invalidation on menu save → `app/Services/MenuService.php`
- [ ] [T055] [P3] Implement mobile hamburger menu support in navigation component → `resources/views/components/navigation.blade.php`
- [ ] [T056] [P3] Add menu items bulk actions: delete selected → `app/Filament/Resources/MenuResource.php`
- [ ] [T057] [P1] Run full test suite and fix any failures
- [ ] [T058] [P1] Run `vendor/bin/pint --dirty` to fix code style
- [ ] [T059] [P1] Update README or documentation if needed
- [ ] [T060] [P1] Create PR and merge to main branch

---

## Summary

| Phase | Tasks | Priority Focus |
|-------|-------|----------------|
| Setup | 2 | P1 |
| Foundational | 8 | P1 |
| US1 – Create Menu | 11 | P1 🎯 MVP |
| US2 – Drag-and-Drop | 9 | P1 🎯 MVP |
| US3 – Item Types | 6 | P2 |
| US4 – Locations | 6 | P2 |
| US5 – Properties | 7 | P3 |
| Polish | 11 | Mixed |

**Total Tasks: 60**
