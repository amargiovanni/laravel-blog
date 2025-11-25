# Tasks – 003-static-pages

> Generated from spec.md, plan.md, and design artifacts.

---

## Phase 1: Setup

- [ ] [T001] [P1] Create feature branch `003-static-pages` from main (if not exists)
- [ ] [T002] [P1] Verify PHP 8.4+, Laravel 12, Filament 4.x are installed and configured

---

## Phase 2: Foundational

- [ ] [T003] [P1] Create migration for `pages` table with all fields: id, title, slug, content, excerpt, parent_id, author_id, status, template, published_at, featured_image_id, meta_title, meta_description, focus_keyword, sort_order, timestamps → `database/migrations/xxxx_create_pages_table.php`
- [ ] [T004] [P1] Create Page model with fillable, casts, and basic relationships (author, parent, children, featuredImage) → `app/Models/Page.php`
- [ ] [T005] [P1] Create PageFactory with all field definitions → `database/factories/PageFactory.php`
- [ ] [T006] [P1] Create PageSeeder for development data → `database/seeders/PageSeeder.php`
- [ ] [T007] [P1] Run migration and verify database schema

---

## Phase 3: US1 – Create and Publish a Static Page (P1) 🎯 MVP

- [ ] [T008] [P1] [US1] Create PageResource with basic CRUD operations → `app/Filament/Resources/PageResource.php`
- [ ] [T009] [P1] [US1] Implement form schema with title, slug (auto-generated), content (RichEditor), excerpt fields → `app/Filament/Resources/PageResource.php`
- [ ] [T010] [P1] [US1] Add status field with enum options (draft, published, scheduled) → `app/Filament/Resources/PageResource.php`
- [ ] [T011] [P1] [US1] Implement slug auto-generation from title with uniqueness validation → `app/Models/Page.php`
- [ ] [T012] [P1] [US1] Create PagePolicy with viewAny, view, create, update, delete methods → `app/Policies/PagePolicy.php`
- [ ] [T013] [P1] [US1] Register PagePolicy in AuthServiceProvider → `app/Providers/AuthServiceProvider.php`
- [ ] [T014] [P1] [US1] Create table columns for title, slug, status, author, updated_at → `app/Filament/Resources/PageResource.php`
- [ ] [T015] [P1] [US1] Add filters for status and author → `app/Filament/Resources/PageResource.php`
- [ ] [T016] [P1] [US1] Write feature tests for page CRUD operations → `tests/Feature/PageTest.php`
- [ ] [T017] [P1] [US1] Write unit tests for Page model → `tests/Unit/Models/PageTest.php`

---

## Phase 4: US2 – Organize Pages in Hierarchy (P2)

- [ ] [T018] [P2] [US2] Add parent_id select field with self-referential relationship → `app/Filament/Resources/PageResource.php`
- [ ] [T019] [P2] [US2] Implement parent/children relationships in Page model → `app/Models/Page.php`
- [ ] [T020] [P2] [US2] Add circular reference validation (page cannot be its own parent/ancestor) → `app/Models/Page.php`
- [ ] [T021] [P2] [US2] Implement breadcrumb generation method for nested pages → `app/Models/Page.php`
- [ ] [T022] [P2] [US2] Add sort_order field for sibling ordering → `app/Filament/Resources/PageResource.php`
- [ ] [T023] [P2] [US2] Create hierarchical page tree view in admin → `app/Filament/Resources/PageResource/Pages/ListPages.php`
- [ ] [T024] [P2] [US2] Write tests for hierarchical page relationships → `tests/Feature/PageHierarchyTest.php`

---

## Phase 5: US3 – Manage Page SEO Settings (P2)

- [ ] [T025] [P2] [US3] Add SEO fieldset with meta_title, meta_description, focus_keyword → `app/Filament/Resources/PageResource.php`
- [ ] [T026] [P2] [US3] Implement character counter for meta_title (60 chars) and meta_description (160 chars) → `app/Filament/Resources/PageResource.php`
- [ ] [T027] [P2] [US3] Add SEO preview component showing Google SERP appearance → `app/Filament/Resources/PageResource.php`
- [ ] [T028] [P2] [US3] Create PageController for frontend page rendering → `app/Http/Controllers/PageController.php`
- [ ] [T029] [P2] [US3] Implement meta tag rendering in page view → `resources/views/pages/show.blade.php`
- [ ] [T030] [P2] [US3] Write tests for SEO field validation and rendering → `tests/Feature/PageSeoTest.php`

---

## Phase 6: US4 – Select Page Template (P3)

- [ ] [T031] [P3] [US4] Add template select field with available templates → `app/Filament/Resources/PageResource.php`
- [ ] [T032] [P3] [US4] Create default page template → `resources/views/pages/templates/default.blade.php`
- [ ] [T033] [P3] [US4] Create full-width page template → `resources/views/pages/templates/full-width.blade.php`
- [ ] [T034] [P3] [US4] Create sidebar page template → `resources/views/pages/templates/sidebar.blade.php`
- [ ] [T035] [P3] [US4] Create landing page template → `resources/views/pages/templates/landing.blade.php`
- [ ] [T036] [P3] [US4] Implement template discovery service to scan available templates → `app/Services/PageTemplateService.php`
- [ ] [T037] [P3] [US4] Update PageController to render selected template → `app/Http/Controllers/PageController.php`
- [ ] [T038] [P3] [US4] Write tests for template selection and rendering → `tests/Feature/PageTemplateTest.php`

---

## Phase 7: US5 – Draft and Schedule Pages (P3)

- [ ] [T039] [P3] [US5] Add published_at datetime picker field → `app/Filament/Resources/PageResource.php`
- [ ] [T040] [P3] [US5] Implement status auto-update logic (draft→scheduled when future date set) → `app/Models/Page.php`
- [ ] [T041] [P3] [US5] Create PublishScheduledPages Artisan command → `app/Console/Commands/PublishScheduledPages.php`
- [ ] [T042] [P3] [US5] Register scheduled command to run every minute → `routes/console.php`
- [ ] [T043] [P3] [US5] Add "Preview" action for draft pages → `app/Filament/Resources/PageResource.php`
- [ ] [T044] [P3] [US5] Implement preview route with draft token validation → `app/Http/Controllers/PageController.php`
- [ ] [T045] [P3] [US5] Write tests for scheduling and auto-publish functionality → `tests/Feature/PageSchedulingTest.php`

---

## Phase 8: Polish & Cross-Cutting Concerns

- [ ] [T046] [P2] Create frontend routes for pages: `/{slug}`, `/{parent}/{slug}`, `/{grandparent}/{parent}/{slug}` → `routes/web.php`
- [ ] [T047] [P2] Implement nested slug resolution in PageController → `app/Http/Controllers/PageController.php`
- [ ] [T048] [P2] Add featured_image_id relationship and media picker → `app/Filament/Resources/PageResource.php`
- [ ] [T049] [P3] Add bulk actions: publish, unpublish, delete → `app/Filament/Resources/PageResource.php`
- [ ] [T050] [P3] Implement page revision tracking (optional, if time permits) → `app/Models/PageRevision.php`
- [ ] [T051] [P1] Run full test suite and fix any failures
- [ ] [T052] [P1] Run `vendor/bin/pint --dirty` to fix code style
- [ ] [T053] [P1] Update README or documentation if needed
- [ ] [T054] [P1] Create PR and merge to main branch

---

## Summary

| Phase | Tasks | Priority Focus |
|-------|-------|----------------|
| Setup | 2 | P1 |
| Foundational | 5 | P1 |
| US1 – Create and Publish | 10 | P1 🎯 MVP |
| US2 – Hierarchy | 7 | P2 |
| US3 – SEO Settings | 6 | P2 |
| US4 – Templates | 8 | P3 |
| US5 – Scheduling | 7 | P3 |
| Polish | 9 | Mixed |

**Total Tasks: 54**
