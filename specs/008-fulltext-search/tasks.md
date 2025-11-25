# Tasks – 008-fulltext-search

> Generated from spec.md, plan.md, and design artifacts.

---

## Phase 1: Setup

- [ ] [T001] [P1] Create feature branch `008-fulltext-search` from main (if not exists)
- [ ] [T002] [P1] Install Laravel Scout package → `composer require laravel/scout`
- [ ] [T003] [P1] Publish Scout configuration → `php artisan vendor:publish --provider="Laravel\Scout\ScoutServiceProvider"`
- [ ] [T004] [P1] Configure Scout driver (database for dev, meilisearch for prod) → `config/scout.php`

---

## Phase 2: Foundational

- [ ] [T005] [P1] Add Searchable trait to Post model → `app/Models/Post.php`
- [ ] [T006] [P1] Implement toSearchableArray() in Post with title, content, excerpt, category names, tag names → `app/Models/Post.php`
- [ ] [T007] [P1] Add Searchable trait to Page model → `app/Models/Page.php`
- [ ] [T008] [P1] Implement toSearchableArray() in Page with title and content → `app/Models/Page.php`
- [ ] [T009] [P1] Create SearchService for unified search across models → `app/Services/SearchService.php`
- [ ] [T010] [P1] Import existing posts/pages into search index → `php artisan scout:import`

---

## Phase 3: US1 – Search for Content (P1) 🎯 MVP

- [ ] [T011] [P1] [US1] Create SearchController with results method → `app/Http/Controllers/SearchController.php`
- [ ] [T012] [P1] [US1] Register route GET /search → `routes/web.php`
- [ ] [T013] [P1] [US1] Implement search query validation (min 2 chars, max 100 chars) → `app/Http/Controllers/SearchController.php`
- [ ] [T014] [P1] [US1] Create search/results.blade.php for displaying results → `resources/views/search/results.blade.php`
- [ ] [T015] [P1] [US1] Display results with title, excerpt, and date → `resources/views/search/results.blade.php`
- [ ] [T016] [P1] [US1] Implement search term highlighting in excerpts → `app/Services/SearchService.php`
- [ ] [T017] [P1] [US1] Add pagination to search results (10 per page) → `app/Http/Controllers/SearchController.php`
- [ ] [T018] [P1] [US1] Display "No results found" message with suggestions → `resources/views/search/results.blade.php`
- [ ] [T019] [P1] [US1] Write feature tests for basic search → `tests/Feature/SearchTest.php`

---

## Phase 4: US2 – Search Across Multiple Content Types (P1) 🎯 MVP

- [ ] [T020] [P1] [US2] Include category names in Post searchable array → `app/Models/Post.php`
- [ ] [T021] [P1] [US2] Include tag names in Post searchable array → `app/Models/Post.php`
- [ ] [T022] [P1] [US2] Implement multi-model search in SearchService (posts + pages) → `app/Services/SearchService.php`
- [ ] [T023] [P1] [US2] Merge and sort results from multiple models → `app/Services/SearchService.php`
- [ ] [T024] [P1] [US2] Exclude drafts and private posts from search results → `app/Models/Post.php`
- [ ] [T025] [P1] [US2] Write tests for category/tag search matching → `tests/Feature/SearchContentTypesTest.php`

---

## Phase 5: US3 – Get Relevant Results First (P2)

- [ ] [T026] [P2] [US3] Implement relevance scoring in SearchService → `app/Services/SearchService.php`
- [ ] [T027] [P2] [US3] Weight title matches higher than content matches → `app/Services/SearchService.php`
- [ ] [T028] [P2] [US3] Boost exact phrase matches over scattered words → `app/Services/SearchService.php`
- [ ] [T029] [P2] [US3] Use recency as tie-breaker for equal relevance → `app/Services/SearchService.php`
- [ ] [T030] [P2] [US3] Sort results by combined relevance + recency score → `app/Services/SearchService.php`
- [ ] [T031] [P2] [US3] Write tests for relevance ranking → `tests/Feature/SearchRankingTest.php`

---

## Phase 6: US4 – Quick Search Suggestions (P3)

- [ ] [T032] [P3] [US4] Create SearchBox Livewire component for auto-suggest → `app/Livewire/SearchBox.php`
- [ ] [T033] [P3] [US4] Create search-box blade view with input and suggestions dropdown → `resources/views/livewire/search-box.blade.php`
- [ ] [T034] [P3] [US4] Register route GET /api/search/suggest for AJAX suggestions → `routes/web.php`
- [ ] [T035] [P3] [US4] Implement suggest() method returning top 5 matching titles → `app/Services/SearchService.php`
- [ ] [T036] [P3] [US4] Add debounce (200ms) to search input for performance → `resources/views/livewire/search-box.blade.php`
- [ ] [T037] [P3] [US4] Handle suggestion click to perform full search → `app/Livewire/SearchBox.php`
- [ ] [T038] [P3] [US4] Hide suggestions gracefully when no matches → `resources/views/livewire/search-box.blade.php`
- [ ] [T039] [P3] [US4] Write tests for search suggestions → `tests/Feature/SearchSuggestionsTest.php`

---

## Phase 7: Polish & Cross-Cutting Concerns

- [ ] [T040] [P2] Implement case-insensitive search → `app/Services/SearchService.php`
- [ ] [T041] [P2] Handle special characters safely (escape quotes, ampersands) → `app/Services/SearchService.php`
- [ ] [T042] [P3] Add fuzzy matching for minor typos (if using Meilisearch) → `config/scout.php`
- [ ] [T043] [P2] Add search form to blog header/navigation → `resources/views/layouts/blog.blade.php`
- [ ] [T044] [P2] Include SearchBox component in Search widget → `app/Widgets/SearchWidget.php`
- [ ] [T045] [P3] Log search queries for analytics (optional) → `app/Services/SearchService.php`
- [ ] [T046] [P3] Add SEO meta tags to search results page → `resources/views/search/results.blade.php`
- [ ] [T047] [P1] Run full test suite and fix any failures
- [ ] [T048] [P1] Run `vendor/bin/pint --dirty` to fix code style
- [ ] [T049] [P1] Update README or documentation if needed
- [ ] [T050] [P1] Create PR and merge to main branch

---

## Summary

| Phase | Tasks | Priority Focus |
|-------|-------|----------------|
| Setup | 4 | P1 |
| Foundational | 6 | P1 |
| US1 – Basic Search | 9 | P1 🎯 MVP |
| US2 – Multi-Content | 6 | P1 🎯 MVP |
| US3 – Relevance | 6 | P2 |
| US4 – Suggestions | 8 | P3 |
| Polish | 11 | Mixed |

**Total Tasks: 50**
