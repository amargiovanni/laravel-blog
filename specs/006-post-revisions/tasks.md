# Tasks – 006-post-revisions

> Generated from spec.md, plan.md, and design artifacts.

---

## Phase 1: Setup

- [ ] [T001] [P1] Create feature branch `006-post-revisions` from main (if not exists)
- [ ] [T002] [P1] Verify PHP 8.4+, Laravel 12, Filament 4.x dependencies
- [ ] [T003] [P1] Install jfcherng/php-diff package for diff comparison → `composer.json`

---

## Phase 2: Foundational

- [ ] [T004] [P1] Create migration for `revisions` table with fields: id, revisionable_type, revisionable_id, user_id, revision_number, title, content, excerpt, metadata (json), is_autosave, is_protected, created_at → `database/migrations/xxxx_create_revisions_table.php`
- [ ] [T005] [P1] Create Revision model with fillable, casts, and relationships (revisionable, user) → `app/Models/Revision.php`
- [ ] [T006] [P1] Add HasRevisions trait for Post and Page models → `app/Traits/HasRevisions.php`
- [ ] [T007] [P1] Apply HasRevisions trait to Post model → `app/Models/Post.php`
- [ ] [T008] [P1] Create RevisionFactory → `database/factories/RevisionFactory.php`
- [ ] [T009] [P1] Run migration and verify database schema

---

## Phase 3: US1 – View Post Revision History (P1) 🎯 MVP

- [ ] [T010] [P1] [US1] Create RevisionObserver to auto-create revision on model save → `app/Observers/RevisionObserver.php`
- [ ] [T011] [P1] [US1] Register RevisionObserver for Post model → `app/Providers/AppServiceProvider.php`
- [ ] [T012] [P1] [US1] Implement revision_number auto-increment in RevisionObserver → `app/Observers/RevisionObserver.php`
- [ ] [T013] [P1] [US1] Create RevisionService with getRevisions() method → `app/Services/RevisionService.php`
- [ ] [T014] [P1] [US1] Create RevisionPanel Livewire component for Filament → `app/Livewire/RevisionPanel.php`
- [ ] [T015] [P1] [US1] Create revision-panel blade view with chronological list → `resources/views/livewire/revision-panel.blade.php`
- [ ] [T016] [P1] [US1] Integrate RevisionPanel into PostResource edit form → `app/Filament/Resources/PostResource.php`
- [ ] [T017] [P1] [US1] Display revision entry: timestamp, author name, revision number → `resources/views/livewire/revision-panel.blade.php`
- [ ] [T018] [P1] [US1] Add pagination for posts with many revisions → `app/Livewire/RevisionPanel.php`
- [ ] [T019] [P1] [US1] Write feature tests for revision creation on save → `tests/Feature/RevisionTest.php`
- [ ] [T020] [P1] [US1] Write unit tests for Revision model → `tests/Unit/Models/RevisionTest.php`

---

## Phase 4: US2 – Compare Revision Differences (P1) 🎯 MVP

- [ ] [T021] [P1] [US2] Implement getDiff() method in RevisionService using jfcherng/php-diff → `app/Services/RevisionService.php`
- [ ] [T022] [P1] [US2] Add compareRevisions() action to RevisionPanel → `app/Livewire/RevisionPanel.php`
- [ ] [T023] [P1] [US2] Create comparison modal/view with side-by-side display → `resources/views/livewire/revision-panel.blade.php`
- [ ] [T024] [P1] [US2] Implement diff highlighting: green for additions, red for deletions → `resources/views/livewire/revision-panel.blade.php`
- [ ] [T025] [P1] [US2] Style diff output with CSS for readability → `resources/css/revisions.css`
- [ ] [T026] [P1] [US2] Compare title and metadata fields in addition to content → `app/Services/RevisionService.php`
- [ ] [T027] [P1] [US2] Write tests for diff comparison functionality → `tests/Feature/RevisionDiffTest.php`

---

## Phase 5: US3 – Restore Previous Revision (P1) 🎯 MVP

- [ ] [T028] [P1] [US3] Implement restore() method in RevisionService → `app/Services/RevisionService.php`
- [ ] [T029] [P1] [US3] Add restoreRevision action with confirmation dialog → `app/Livewire/RevisionPanel.php`
- [ ] [T030] [P1] [US3] Create new revision on restore (to preserve current state) → `app/Services/RevisionService.php`
- [ ] [T031] [P1] [US3] Mark restoration revisions with metadata flag → `app/Services/RevisionService.php`
- [ ] [T032] [P1] [US3] Update post content from selected revision → `app/Services/RevisionService.php`
- [ ] [T033] [P1] [US3] Show success notification after restore → `app/Livewire/RevisionPanel.php`
- [ ] [T034] [P1] [US3] Write tests for revision restoration → `tests/Feature/RevisionRestoreTest.php`

---

## Phase 6: US4 – Auto-Save Drafts as Revisions (P2)

- [ ] [T035] [P2] [US4] Create AutoSaveService with interval-based save logic → `app/Services/AutoSaveService.php`
- [ ] [T036] [P2] [US4] Add Livewire autosave component for editor → `app/Livewire/AutoSave.php`
- [ ] [T037] [P2] [US4] Implement JavaScript timer for 60-second auto-save trigger → `resources/js/autosave.js`
- [ ] [T038] [P2] [US4] Mark auto-save revisions with is_autosave=true → `app/Services/AutoSaveService.php`
- [ ] [T039] [P2] [US4] Display auto-save indicator distinct from manual saves in history → `resources/views/livewire/revision-panel.blade.php`
- [ ] [T040] [P2] [US4] Implement auto-save recovery on editor load → `app/Livewire/AutoSave.php`
- [ ] [T041] [P2] [US4] Consolidate auto-saves when manual save occurs → `app/Services/RevisionService.php`
- [ ] [T042] [P2] [US4] Add autosave interval to config → `config/revisions.php`
- [ ] [T043] [P2] [US4] Write tests for auto-save functionality → `tests/Feature/AutoSaveTest.php`

---

## Phase 7: US5 – Manage Revision Storage (P3)

- [ ] [T044] [P3] [US5] Add revision_limit setting to config → `config/revisions.php`
- [ ] [T045] [P3] [US5] Implement pruneOldRevisions() method in RevisionService → `app/Services/RevisionService.php`
- [ ] [T046] [P3] [US5] Call prune after new revision creation → `app/Observers/RevisionObserver.php`
- [ ] [T047] [P3] [US5] Add toggleProtection action in RevisionPanel → `app/Livewire/RevisionPanel.php`
- [ ] [T048] [P3] [US5] Exclude protected revisions (is_protected=true) from auto-delete → `app/Services/RevisionService.php`
- [ ] [T049] [P3] [US5] Create Filament settings page for revision configuration → `app/Filament/Pages/RevisionSettings.php`
- [ ] [T050] [P3] [US5] Write tests for revision pruning and protection → `tests/Feature/RevisionPruningTest.php`

---

## Phase 8: Polish & Cross-Cutting Concerns

- [ ] [T051] [P2] Apply HasRevisions trait to Page model → `app/Models/Page.php`
- [ ] [T052] [P2] Integrate RevisionPanel into PageResource → `app/Filament/Resources/PageResource.php`
- [ ] [T053] [P2] Store metadata (categories, tags, featured_image_id) in revision → `app/Observers/RevisionObserver.php`
- [ ] [T054] [P2] Restore metadata along with content → `app/Services/RevisionService.php`
- [ ] [T055] [P2] Handle missing media on restore (warn user) → `app/Services/RevisionService.php`
- [ ] [T056] [P3] Create RevisionPolicy with view, restore permissions → `app/Policies/RevisionPolicy.php`
- [ ] [T057] [P3] Register RevisionPolicy → `app/Providers/AuthServiceProvider.php`
- [ ] [T058] [P3] Add keyboard shortcut for manual save with revision note → `app/Livewire/RevisionPanel.php`
- [ ] [T059] [P1] Run full test suite and fix any failures
- [ ] [T060] [P1] Run `vendor/bin/pint --dirty` to fix code style
- [ ] [T061] [P1] Update README or documentation if needed
- [ ] [T062] [P1] Create PR and merge to main branch

---

## Summary

| Phase | Tasks | Priority Focus |
|-------|-------|----------------|
| Setup | 3 | P1 |
| Foundational | 6 | P1 |
| US1 – View History | 11 | P1 🎯 MVP |
| US2 – Compare Diffs | 7 | P1 🎯 MVP |
| US3 – Restore Revision | 7 | P1 🎯 MVP |
| US4 – Auto-Save | 9 | P2 |
| US5 – Storage Management | 7 | P3 |
| Polish | 12 | Mixed |

**Total Tasks: 62**
