# Tasks – 007-public-frontend

> Generated from spec.md, plan.md, and design artifacts.

---

## Phase 1: Setup

- [ ] [T001] [P1] Create feature branch `007-public-frontend` from main (if not exists)
- [ ] [T002] [P1] Verify PHP 8.4+, Laravel 12, Livewire 3, TailwindCSS 4, Flux UI dependencies
- [ ] [T003] [P1] Run `npm run build` to ensure frontend assets are compiled

---

## Phase 2: Foundational

- [ ] [T004] [P1] Create main blog layout template with header, main, sidebar, footer areas → `resources/views/layouts/blog.blade.php`
- [ ] [T005] [P1] Implement TailwindCSS responsive utilities (320px-2560px) → `resources/views/layouts/blog.blade.php`
- [ ] [T006] [P1] Add dark mode support with system preference detection → `resources/views/layouts/blog.blade.php`
- [ ] [T007] [P1] Create post-card Blade component for post list items → `resources/views/components/post-card.blade.php`
- [ ] [T008] [P1] Create pagination Blade component → `resources/views/components/pagination.blade.php`
- [ ] [T009] [P1] Create sidebar Blade component with widget area integration → `resources/views/components/sidebar.blade.php`
- [ ] [T010] [P1] Create seo-meta Blade component for meta tags → `resources/views/components/seo-meta.blade.php`
- [ ] [T011] [P1] Integrate navigation menu component in header → `resources/views/layouts/blog.blade.php`

---

## Phase 3: US1 – View Blog Homepage (P1) 🎯 MVP

- [ ] [T012] [P1] [US1] Create HomeController with index method → `app/Http/Controllers/HomeController.php`
- [ ] [T013] [P1] [US1] Register home route GET / → `routes/web.php`
- [ ] [T014] [P1] [US1] Create home.blade.php with featured posts section → `resources/views/home.blade.php`
- [ ] [T015] [P1] [US1] Query recent published posts with eager loading (author, categories, featured image) → `app/Http/Controllers/HomeController.php`
- [ ] [T016] [P1] [US1] Display post cards with title, excerpt, author, date, featured image → `resources/views/home.blade.php`
- [ ] [T017] [P1] [US1] Add pagination (10 posts per page) → `app/Http/Controllers/HomeController.php`
- [ ] [T018] [P1] [US1] Handle empty state with "No posts yet" message → `resources/views/home.blade.php`
- [ ] [T019] [P1] [US1] Write feature tests for homepage → `tests/Feature/HomePageTest.php`
- [ ] [T020] [P1] [US1] Write browser tests for homepage responsiveness → `tests/Browser/HomePageTest.php`

---

## Phase 4: US2 – Read a Single Post (P1) 🎯 MVP

- [ ] [T021] [P1] [US2] Create PostController with index and show methods → `app/Http/Controllers/PostController.php`
- [ ] [T022] [P1] [US2] Register routes GET /blog, GET /blog/{slug} → `routes/web.php`
- [ ] [T023] [P1] [US2] Create posts/index.blade.php for post listing → `resources/views/posts/index.blade.php`
- [ ] [T024] [P1] [US2] Create posts/show.blade.php for single post view → `resources/views/posts/show.blade.php`
- [ ] [T025] [P1] [US2] Display full post content with rich formatting → `resources/views/posts/show.blade.php`
- [ ] [T026] [P1] [US2] Show metadata: author, date, categories, tags → `resources/views/posts/show.blade.php`
- [ ] [T027] [P1] [US2] Display featured image prominently at top → `resources/views/posts/show.blade.php`
- [ ] [T028] [P1] [US2] Include comments section (if comments enabled) → `resources/views/posts/show.blade.php`
- [ ] [T029] [P1] [US2] Set SEO meta tags (title, description, Open Graph, Twitter Cards) → `resources/views/posts/show.blade.php`
- [ ] [T030] [P1] [US2] Return 404 for non-existent or unpublished posts → `app/Http/Controllers/PostController.php`
- [ ] [T031] [P1] [US2] Write feature tests for single post page → `tests/Feature/PostPageTest.php`

---

## Phase 5: US3 – Browse Posts by Category (P2)

- [ ] [T032] [P2] [US3] Create CategoryController with show method → `app/Http/Controllers/CategoryController.php`
- [ ] [T033] [P2] [US3] Register route GET /category/{slug} → `routes/web.php`
- [ ] [T034] [P2] [US3] Create categories/show.blade.php for category archive → `resources/views/categories/show.blade.php`
- [ ] [T035] [P2] [US3] Display category name and description at top → `resources/views/categories/show.blade.php`
- [ ] [T036] [P2] [US3] List only posts in selected category with pagination → `app/Http/Controllers/CategoryController.php`
- [ ] [T037] [P2] [US3] Show breadcrumb for nested categories (parent > child) → `resources/views/categories/show.blade.php`
- [ ] [T038] [P2] [US3] Write feature tests for category page → `tests/Feature/CategoryPageTest.php`

---

## Phase 6: US4 – Browse Posts by Tag (P2)

- [ ] [T039] [P2] [US4] Create TagController with show method → `app/Http/Controllers/TagController.php`
- [ ] [T040] [P2] [US4] Register route GET /tag/{slug} → `routes/web.php`
- [ ] [T041] [P2] [US4] Create tags/show.blade.php for tag archive → `resources/views/tags/show.blade.php`
- [ ] [T042] [P2] [US4] Display tag name as page title → `resources/views/tags/show.blade.php`
- [ ] [T043] [P2] [US4] List only posts with selected tag with pagination → `app/Http/Controllers/TagController.php`
- [ ] [T044] [P2] [US4] Write feature tests for tag page → `tests/Feature/TagPageTest.php`

---

## Phase 7: US5 – View Author Page (P3)

- [ ] [T045] [P3] [US5] Create AuthorController with show method → `app/Http/Controllers/AuthorController.php`
- [ ] [T046] [P3] [US5] Register route GET /author/{username} → `routes/web.php`
- [ ] [T047] [P3] [US5] Create authors/show.blade.php for author profile → `resources/views/authors/show.blade.php`
- [ ] [T048] [P3] [US5] Display author name, avatar, and bio → `resources/views/authors/show.blade.php`
- [ ] [T049] [P3] [US5] List only author's posts with pagination → `app/Http/Controllers/AuthorController.php`
- [ ] [T050] [P3] [US5] Write feature tests for author page → `tests/Feature/AuthorPageTest.php`

---

## Phase 8: US6 – Browse Archives by Date (P3)

- [ ] [T051] [P3] [US6] Create ArchiveController with show method → `app/Http/Controllers/ArchiveController.php`
- [ ] [T052] [P3] [US6] Register route GET /archive/{year}/{month?} → `routes/web.php`
- [ ] [T053] [P3] [US6] Create archives/show.blade.php for date archive → `resources/views/archives/show.blade.php`
- [ ] [T054] [P3] [US6] Display selected period in title (e.g., "January 2025") → `resources/views/archives/show.blade.php`
- [ ] [T055] [P3] [US6] List posts from selected period with pagination → `app/Http/Controllers/ArchiveController.php`
- [ ] [T056] [P3] [US6] Handle empty periods with "No posts" message → `resources/views/archives/show.blade.php`
- [ ] [T057] [P3] [US6] Write feature tests for archive page → `tests/Feature/ArchivePageTest.php`

---

## Phase 9: Polish & Cross-Cutting Concerns

- [ ] [T058] [P1] Create PageController for static pages → `app/Http/Controllers/PageController.php`
- [ ] [T059] [P1] Register catch-all route GET /{slug} for pages → `routes/web.php`
- [ ] [T060] [P1] Create pages/show.blade.php for single page view → `resources/views/pages/show.blade.php`
- [ ] [T061] [P1] Create custom 404 error page → `resources/views/errors/404.blade.php`
- [ ] [T062] [P2] Create SearchController with results method → `app/Http/Controllers/SearchController.php`
- [ ] [T063] [P2] Register route GET /search → `routes/web.php`
- [ ] [T064] [P2] Create search/results.blade.php for search results → `resources/views/search/results.blade.php`
- [ ] [T065] [P2] Add social sharing meta tags (Open Graph, Twitter Cards) to all pages → `resources/views/components/seo-meta.blade.php`
- [ ] [T066] [P3] Add structured data (JSON-LD) for blog posts → `resources/views/posts/show.blade.php`
- [ ] [T067] [P3] Implement placeholder image for posts without featured image → `resources/views/components/post-card.blade.php`
- [ ] [T068] [P1] Run full test suite and fix any failures
- [ ] [T069] [P1] Run `vendor/bin/pint --dirty` to fix code style
- [ ] [T070] [P1] Update README or documentation if needed
- [ ] [T071] [P1] Create PR and merge to main branch

---

## Summary

| Phase | Tasks | Priority Focus |
|-------|-------|----------------|
| Setup | 3 | P1 |
| Foundational | 8 | P1 |
| US1 – Homepage | 9 | P1 🎯 MVP |
| US2 – Single Post | 11 | P1 🎯 MVP |
| US3 – Category | 7 | P2 |
| US4 – Tag | 6 | P2 |
| US5 – Author | 6 | P3 |
| US6 – Archives | 7 | P3 |
| Polish | 14 | Mixed |

**Total Tasks: 71**
