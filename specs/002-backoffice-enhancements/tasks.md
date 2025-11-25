# Tasks: Backoffice Enhancements

**Input**: Design documents from `/specs/002-backoffice-enhancements/`
**Prerequisites**: plan.md, spec.md, research.md, data-model.md, contracts/filament-resources.md, quickstart.md

**Tests**: Test-First Development (TDD) is REQUIRED per constitution. Tests must be written and fail before implementation.

**Organization**: Tasks are grouped by user story to enable independent implementation and testing of each story.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: Which user story this task belongs to (US1-US5)
- Include exact file paths in descriptions

## User Stories Reference

| Story | Title | Priority |
|-------|-------|----------|
| US1 | Media Library Management | P1 |
| US2 | Categories and Tags Management | P1 |
| US3 | User and Role Management | P1 |
| US4 | Theme Management | P2 |
| US5 | Generative Engine Optimization (GEO) | P2 |

---

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Project initialization and basic structure

- [x] T001 Verify PHP 8.4, Laravel 12, Filament 4.x are installed and configured
- [x] T002 [P] Create migration `add_is_active_to_users_table` in database/migrations/
- [x] T003 [P] Run migration: `php artisan migrate`

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Core infrastructure that MUST be complete before ANY user story can be implemented

**CRITICAL**: No user story work can begin until this phase is complete

- [x] T004 Install Filament Shield: `composer require bezhansalleh/filament-shield "^3.0"`
- [x] T005 Publish Shield config: `php artisan vendor:publish --tag="filament-shield-config"`
- [x] T006 Run Shield setup: `php artisan shield:install`
- [x] T007 Update `app/Models/User.php` to add `is_active` field to fillable, casts, and scopes
- [x] T008 Add `isLastAdmin()`, `deactivate()`, `activate()` methods to User model
- [x] T009 Update `database/seeders/RolesAndPermissionsSeeder.php` with new permissions for categories, tags, media, users

**Checkpoint**: Foundation ready - user story implementation can now begin

---

## Phase 3: User Story 2 - Categories and Tags Management (Priority: P1)

**Goal**: Provide dedicated management pages for categories and tags with hierarchy support and bulk operations

**Independent Test**: Create category hierarchies and tags, verify they appear in post forms and frontend filters

### Tests for User Story 2

> **NOTE: Write these tests FIRST, ensure they FAIL before implementation**

- [x] T010 [P] [US2] Create CategoryResource test in tests/Feature/Filament/CategoryResourceTest.php
- [x] T011 [P] [US2] Create TagResource test in tests/Feature/Filament/TagResourceTest.php

### Implementation for User Story 2

- [x] T012 [P] [US2] Create CategoryPolicy in app/Policies/CategoryPolicy.php
- [x] T013 [P] [US2] Create TagPolicy in app/Policies/TagPolicy.php
- [x] T014 [P] [US2] Add `posts_count` accessor and `canDelete()` method to app/Models/Category.php
- [x] T015 [P] [US2] Add `posts_count` accessor and `mergeInto()` method to app/Models/Tag.php
- [x] T016 [US2] Create CategoryResource in app/Filament/Resources/CategoryResource.php
- [x] T017 [US2] Create CategoryResource pages in app/Filament/Resources/CategoryResource/Pages/
- [x] T018 [US2] Create TagResource in app/Filament/Resources/TagResource.php
- [x] T019 [US2] Create TagResource pages in app/Filament/Resources/TagResource/Pages/
- [x] T020 [US2] Implement tag merge bulk action in TagResource
- [x] T021 [US2] Run tests: `php artisan test --filter=CategoryResource`
- [x] T022 [US2] Run tests: `php artisan test --filter=TagResource`

**Checkpoint**: Categories and Tags management fully functional and tested ✅

---

## Phase 4: User Story 1 - Media Library Management (Priority: P1)

**Goal**: Centralized upload, organization, and management of media files with automatic size optimization

**Independent Test**: Upload various file types, organize them, attach to posts, verify thumbnails and usage tracking

### Tests for User Story 1

> **NOTE: Write these tests FIRST, ensure they FAIL before implementation**

- [x] T023 [P] [US1] Create MediaResource test in tests/Feature/Filament/MediaResourceTest.php

### Implementation for User Story 1

- [x] T024 [P] [US1] Create MediaPolicy in app/Policies/MediaPolicy.php
- [x] T024b [US1] Verify ImageService generates thumbnail/medium/large sizes; enhance if needed in app/Services/ImageService.php
- [x] T025 [US1] Add `isUsed()`, `usage_count` accessor, scopes (`unused`, `images`, `documents`) to app/Models/Media.php
- [x] T026 [US1] Add `featuredByPosts()` relationship to Media model
- [x] T027 [US1] Create MediaResource in app/Filament/Resources/MediaResource.php
- [x] T028 [US1] Create MediaResource pages (List, Create, Edit, View) in app/Filament/Resources/MediaResource/Pages/
- [x] T029 [US1] Implement grid/list toggle view in MediaResource ListRecords page (table view with thumbnails)
- [x] T030 [US1] Implement bulk delete for unused media action
- [x] T031 [US1] Update PostResource to use media picker from library for featured image (already implemented via relationship select)
- [x] T032 [US1] Run tests: `php artisan test --filter=MediaResource`

**Checkpoint**: Media Library fully functional and tested ✅

---

## Phase 5: User Story 3 - User and Role Management (Priority: P1)

**Goal**: Full user CRUD with role assignment, deactivation, and last admin protection

**Independent Test**: Create users with different roles, verify access permissions, test deactivation flow

### Tests for User Story 3

> **NOTE: Write these tests FIRST, ensure they FAIL before implementation**

- [x] T033 [P] [US3] Create UserResource test in tests/Feature/Filament/UserResourceTest.php

### Implementation for User Story 3

- [x] T034 [P] [US3] Create UserPolicy in app/Policies/UserPolicy.php
- [x] T035 [US3] Create UserResource in app/Filament/Resources/UserResource.php
- [x] T036 [US3] Create UserResource pages (List, Create, Edit, View) in app/Filament/Resources/UserResource/Pages/
- [x] T037 [US3] Implement deactivate/activate toggle action in UserResource
- [x] T038 [US3] Implement last admin protection in delete action
- [x] T039 [US3] Implement role assignment with CheckboxList in UserResource form
- [x] T039b [US3] Implement welcome email notification on user creation using Laravel notifications
- [x] T040 [US3] Generate Shield permissions: `php artisan shield:generate --all --panel=admin`
- [x] T041 [US3] Run tests: `php artisan test --filter=UserResource`

**Checkpoint**: User and Role management fully functional and tested ✅

---

## Phase 6: User Story 4 - Theme Management (Priority: P2)

**Goal**: Customizable branding settings (colors, logo, footer) stored in Settings model

**Independent Test**: Change theme colors and logo, verify frontend reflects changes

### Tests for User Story 4

> **NOTE: Write these tests FIRST, ensure they FAIL before implementation**

- [x] T042 [P] [US4] Create ThemeSettings test in tests/Feature/Filament/ThemeSettingsTest.php

### Implementation for User Story 4

- [x] T043 [US4] Create ThemeSettings Filament Page in app/Filament/Pages/ThemeSettings.php
- [x] T044 [US4] Implement color pickers, logo/favicon upload, footer configuration in ThemeSettings form
- [x] T045 [US4] Implement "Reset to Defaults" action in ThemeSettings
- [x] T046 [US4] Update resources/views/components/layouts/blog.blade.php to inject CSS variables from theme settings
- [x] T047 [US4] Add theme color variables to frontend stylesheet
- [x] T048 [US4] Run tests: `php artisan test --filter=ThemeSettings`

**Checkpoint**: Theme Management fully functional and tested ✅

---

## Phase 7: User Story 5 - Generative Engine Optimization (Priority: P2)

**Goal**: Automatic llms.txt generation and JSON-LD structured data for AI discoverability

**Independent Test**: Publish posts, verify /llms.txt endpoint returns valid content, verify JSON-LD in page source

### Tests for User Story 5

> **NOTE: Write these tests FIRST, ensure they FAIL before implementation**

- [x] T049 [P] [US5] Create LlmsTxtService unit test in tests/Unit/Services/LlmsTxtServiceTest.php
- [x] T050 [P] [US5] Create JsonLdService unit test in tests/Unit/Services/JsonLdServiceTest.php
- [x] T051 [P] [US5] Create llms.txt feature test in tests/Feature/GEO/LlmsTxtTest.php
- [x] T052 [P] [US5] Create JSON-LD feature test in tests/Feature/GEO/JsonLdTest.php

### Implementation for User Story 5

- [x] T053 [US5] Create LlmsTxtService in app/Services/LlmsTxtService.php
- [x] T054 [US5] Create JsonLdService in app/Services/JsonLdService.php
- [x] T055 [US5] Add /llms.txt route in routes/web.php with caching
- [x] T056 [US5] Create GeoSettings Filament Page in app/Filament/Pages/GeoSettings.php
- [x] T057 [US5] Implement llms.txt preview and configuration in GeoSettings
- [x] T057b [US5] Add llms.txt format validation with error reporting in GeoSettings dashboard section
- [x] T058 [US5] Update PostObserver in app/Observers/PostObserver.php to regenerate llms.txt on post publish/update/delete
- [x] T059 [US5] Create JSON-LD Blade component in resources/views/components/json-ld.blade.php
- [x] T060 [US5] Integrate JSON-LD component into blog layout for posts
- [x] T061 [US5] Run tests: `php artisan test --filter=LlmsTxt`
- [x] T062 [US5] Run tests: `php artisan test --filter=JsonLd`

**Checkpoint**: GEO features fully functional and tested ✅

---

## Phase 8: Polish & Cross-Cutting Concerns

**Purpose**: Improvements that affect multiple user stories

- [x] T063 [P] Run Pint to fix code style: `vendor/bin/pint --dirty`
- [ ] T064 [P] Run Larastan static analysis: `./vendor/bin/phpstan analyse`
- [x] T065 Run full test suite: `php artisan test` (192 tests passing)
- [x] T066 Build frontend assets: `npm run build`
- [x] T067 Clear caches: `php artisan cache:clear && php artisan config:clear && php artisan view:clear`
- [ ] T068 Manual verification: Test all Filament resources in browser at /admin
- [ ] T069 Verify permissions restrict access correctly for each role

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies - can start immediately
- **Foundational (Phase 2)**: Depends on Setup completion - BLOCKS all user stories
- **User Stories (Phases 3-7)**: All depend on Foundational phase completion
  - Can proceed in parallel (if staffed)
  - Or sequentially in priority order (P1 → P2)
- **Polish (Phase 8)**: Depends on all desired user stories being complete

### User Story Dependencies

| Story | Depends On | Can Parallel With |
|-------|------------|-------------------|
| US2 (Categories/Tags) | Foundational | US1, US3 |
| US1 (Media Library) | Foundational | US2, US3 |
| US3 (User Management) | Foundational | US1, US2 |
| US4 (Theme) | Foundational | US5 |
| US5 (GEO) | Foundational | US4 |

### Within Each User Story

1. Tests MUST be written and FAIL before implementation
2. Policies before Resources
3. Model enhancements before Resources
4. Resources before integrations
5. Run story-specific tests after implementation

### Parallel Opportunities

- Setup phase: T002 and T003 can run in parallel (after T001)
- Foundational phase: T004-T009 are sequential (Shield must install first)
- US2: T010-T015 can all run in parallel (tests + policies + model enhancements)
- US1: T023-T024 can run in parallel
- US3: T033-T034 can run in parallel
- US5: T049-T052 can all run in parallel (all tests)

---

## Parallel Example: User Story 2 Tests

```bash
# Launch all tests for User Story 2 together:
Task: "Create CategoryResource test in tests/Feature/Filament/CategoryResourceTest.php"
Task: "Create TagResource test in tests/Feature/Filament/TagResourceTest.php"

# Launch all model/policy tasks together:
Task: "Create CategoryPolicy in app/Policies/CategoryPolicy.php"
Task: "Create TagPolicy in app/Policies/TagPolicy.php"
Task: "Add posts_count accessor and canDelete() method to app/Models/Category.php"
Task: "Add posts_count accessor and mergeInto() method to app/Models/Tag.php"
```

---

## Implementation Strategy

### MVP First (P1 User Stories Only)

1. Complete Phase 1: Setup
2. Complete Phase 2: Foundational (CRITICAL - blocks all stories)
3. Complete Phase 3: US2 - Categories & Tags
4. Complete Phase 4: US1 - Media Library
5. Complete Phase 5: US3 - User Management
6. **STOP and VALIDATE**: All P1 stories independently testable
7. Deploy/demo if ready

### Incremental Delivery

1. Complete Setup + Foundational → Foundation ready
2. Add US2 (Categories/Tags) → Test independently → Usable for content organization
3. Add US1 (Media Library) → Test independently → Media management available
4. Add US3 (User Management) → Test independently → Full admin capability (MVP!)
5. Add US4 (Theme) → Test independently → Branding customization
6. Add US5 (GEO) → Test independently → AI discoverability
7. Each story adds value without breaking previous stories

### Recommended Order

For a single developer:
1. Foundational → US2 → US1 → US3 → US4 → US5 → Polish

US2 first because Categories/Tags are simpler and establish the Resource pattern.

---

## Notes

- [P] tasks = different files, no dependencies
- [Story] label maps task to specific user story for traceability
- Each user story should be independently completable and testable
- Verify tests fail before implementing
- Commit after each task or logical group
- Stop at any checkpoint to validate story independently
- Run `vendor/bin/pint --dirty` frequently to maintain code style
