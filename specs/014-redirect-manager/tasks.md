# Tasks – 014-redirect-manager

> Generated from spec.md, plan.md, and design artifacts.

---

## Phase 1: Setup

- [ ] [T001] [P1] Create feature branch `014-redirect-manager` from main (if not exists)
- [ ] [T002] [P1] Verify Laravel Cache driver is configured
- [ ] [T003] [P1] Verify middleware registration location in bootstrap/app.php

---

## Phase 2: Foundational – Database & Model

- [ ] [T004] [P1] Create migration for `redirects` table with fields: id, source_url, target_url, status_code, is_active, is_automatic, hits, last_hit_at, timestamps → `database/migrations/xxxx_create_redirects_table.php`
- [ ] [T005] [P1] Create Redirect model with fillable, casts, scopes (active, automatic, manual), and validation rules → `app/Models/Redirect.php`
- [ ] [T006] [P1] Implement recordHit() method for hit tracking → `app/Models/Redirect.php`
- [ ] [T007] [P1] Create RedirectFactory → `database/factories/RedirectFactory.php`
- [ ] [T008] [P1] Run migration and verify schema

---

## Phase 3: US1 – Create URL Redirect (P1) 🎯 MVP

- [ ] [T009] [P1] [US1] Create HandleRedirects middleware → `app/Http/Middleware/HandleRedirects.php`
- [ ] [T010] [P1] [US1] Register middleware (prepend to stack) → `bootstrap/app.php`
- [ ] [T011] [P1] [US1] Implement redirect matching logic by source URL → `app/Http/Middleware/HandleRedirects.php`
- [ ] [T012] [P1] [US1] Execute 301 permanent redirect with proper status code → `app/Http/Middleware/HandleRedirects.php`
- [ ] [T013] [P1] [US1] Execute 302 temporary redirect with proper status code → `app/Http/Middleware/HandleRedirects.php`
- [ ] [T014] [P1] [US1] Preserve query strings during redirect → `app/Http/Middleware/HandleRedirects.php`
- [ ] [T015] [P1] [US1] Increment hit counter on each redirect → `app/Http/Middleware/HandleRedirects.php`
- [ ] [T016] [P1] [US1] Write tests for 301 redirect → `tests/Feature/RedirectTest.php`
- [ ] [T017] [P1] [US1] Write tests for 302 redirect → `tests/Feature/RedirectTest.php`
- [ ] [T018] [P1] [US1] Write tests for query string preservation → `tests/Feature/RedirectTest.php`

---

## Phase 4: US2 – Manage Existing Redirects (P1) 🎯 MVP

- [ ] [T019] [P1] [US2] Create RedirectResource for Filament → `app/Filament/Resources/RedirectResource.php`
- [ ] [T020] [P1] [US2] Implement create form with source_url, target_url, status_code, is_active fields → `app/Filament/Resources/RedirectResource.php`
- [ ] [T021] [P1] [US2] Implement table with source_url, target_url, status_code, hits, is_active columns → `app/Filament/Resources/RedirectResource.php`
- [ ] [T022] [P1] [US2] Add filters: status code (301/302), active status, automatic/manual → `app/Filament/Resources/RedirectResource.php`
- [ ] [T023] [P1] [US2] Implement edit action → `app/Filament/Resources/RedirectResource.php`
- [ ] [T024] [P1] [US2] Implement delete action → `app/Filament/Resources/RedirectResource.php`
- [ ] [T025] [P1] [US2] Display hit count and last_hit_at in view → `app/Filament/Resources/RedirectResource.php`
- [ ] [T026] [P1] [US2] Write tests for admin CRUD operations → `tests/Feature/RedirectManagementTest.php`

---

## Phase 5: Validation & Safety

- [ ] [T027] [P1] Implement loop detection (prevent circular redirects) → `app/Models/Redirect.php`
- [ ] [T028] [P1] Prevent self-redirect (source = target) → `app/Models/Redirect.php`
- [ ] [T029] [P1] Warn when source URL matches existing content (post/page) → `app/Filament/Resources/RedirectResource.php`
- [ ] [T030] [P1] Validate URL format (must start with /) → `app/Models/Redirect.php`
- [ ] [T031] [P1] Create NotSelfRedirect validation rule → `app/Rules/NotSelfRedirect.php`
- [ ] [T032] [P1] Create NoRedirectLoop validation rule → `app/Rules/NoRedirectLoop.php`
- [ ] [T033] [P1] Write tests for loop prevention → `tests/Feature/RedirectValidationTest.php`

---

## Phase 6: Caching

- [ ] [T034] [P2] Implement per-URL cache for redirect lookups → `app/Http/Middleware/HandleRedirects.php`
- [ ] [T035] [P2] Cache redirect rules with appropriate TTL (1 hour) → `app/Http/Middleware/HandleRedirects.php`
- [ ] [T036] [P2] Invalidate cache on redirect create/update/delete → `app/Models/Redirect.php`
- [ ] [T037] [P2] Create RedirectService for centralized cache management → `app/Services/RedirectService.php`
- [ ] [T038] [P2] Write tests for caching behavior → `tests/Feature/RedirectCacheTest.php`

---

## Phase 7: US3 – Automatic Redirects on Slug Change (P2)

- [ ] [T039] [P2] [US3] Create/update PostObserver for slug change detection → `app/Observers/PostObserver.php`
- [ ] [T040] [P2] [US3] Auto-create 301 redirect when post slug changes → `app/Observers/PostObserver.php`
- [ ] [T041] [P2] [US3] Mark auto-created redirects with is_automatic=true → `app/Observers/PostObserver.php`
- [ ] [T042] [P2] [US3] Create/update PageObserver for page slug changes → `app/Observers/PageObserver.php`
- [ ] [T043] [P2] [US3] Update existing redirect chains when slugs change → `app/Services/RedirectService.php`
- [ ] [T044] [P2] [US3] Write tests for automatic redirect creation → `tests/Feature/AutoRedirectTest.php`

---

## Phase 8: US4 – Import/Export Redirects (P3)

- [ ] [T045] [P3] [US4] Implement CSV export action → `app/Filament/Resources/RedirectResource.php`
- [ ] [T046] [P3] [US4] Create export CSV with columns: source_url, target_url, status_code, is_active, hits → `app/Filament/Resources/RedirectResource.php`
- [ ] [T047] [P3] [US4] Implement CSV import action → `app/Filament/Resources/RedirectResource.php`
- [ ] [T048] [P3] [US4] Validate import data (URL format, no loops) → `app/Services/RedirectImportService.php`
- [ ] [T049] [P3] [US4] Handle duplicates during import (skip and log) → `app/Services/RedirectImportService.php`
- [ ] [T050] [P3] [US4] Display import results (created, skipped, errors) → `app/Filament/Resources/RedirectResource.php`
- [ ] [T051] [P3] [US4] Write tests for import/export functionality → `tests/Feature/RedirectImportExportTest.php`

---

## Phase 9: US5 – Track Redirect Usage (P3)

- [ ] [T052] [P3] [US5] Display hits column with sorting in admin table → `app/Filament/Resources/RedirectResource.php`
- [ ] [T053] [P3] [US5] Update last_hit_at timestamp on each redirect → `app/Models/Redirect.php`
- [ ] [T054] [P3] [US5] Add "unused redirects" filter (no hits in 30 days) → `app/Filament/Resources/RedirectResource.php`
- [ ] [T055] [P3] [US5] Write tests for hit counting accuracy → `tests/Feature/RedirectHitTrackingTest.php`

---

## Phase 10: Polish & Cross-Cutting Concerns

- [ ] [T056] [P2] Create RedirectPolicy with view, create, edit, delete permissions → `app/Policies/RedirectPolicy.php`
- [ ] [T057] [P2] Register RedirectPolicy and restrict to admin roles → `app/Providers/AuthServiceProvider.php`
- [ ] [T058] [P3] Add bulk delete action → `app/Filament/Resources/RedirectResource.php`
- [ ] [T059] [P3] Add toggle active/inactive action → `app/Filament/Resources/RedirectResource.php`
- [ ] [T060] [P1] Run full test suite and fix any failures
- [ ] [T061] [P1] Run `vendor/bin/pint --dirty` to fix code style
- [ ] [T062] [P1] Update README or documentation if needed
- [ ] [T063] [P1] Create PR and merge to main branch

---

## Summary

| Phase | Tasks | Priority Focus |
|-------|-------|----------------|
| Setup | 3 | P1 |
| Foundational | 5 | P1 |
| US1 – Create Redirect | 10 | P1 🎯 MVP |
| US2 – Manage Redirects | 8 | P1 🎯 MVP |
| Validation & Safety | 7 | P1 |
| Caching | 5 | P2 |
| US3 – Auto Redirects | 6 | P2 |
| US4 – Import/Export | 7 | P3 |
| US5 – Track Usage | 4 | P3 |
| Polish | 8 | Mixed |

**Total Tasks: 63**
