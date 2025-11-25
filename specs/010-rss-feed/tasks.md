# Tasks – 010-rss-feed

> Generated from spec.md, plan.md, and design artifacts.

---

## Phase 1: Setup

- [ ] [T001] [P1] Create feature branch `010-rss-feed` from main (if not exists)
- [ ] [T002] [P1] Install spatie/laravel-feed package → `composer require spatie/laravel-feed`
- [ ] [T003] [P1] Publish feed configuration → `php artisan vendor:publish --provider="Spatie\Feed\FeedServiceProvider"`

---

## Phase 2: Foundational

- [ ] [T004] [P1] Configure main feed in config/feed.php → `config/feed.php`
- [ ] [T005] [P1] Implement Feedable interface on Post model → `app/Models/Post.php`
- [ ] [T006] [P1] Implement toFeedItem() method with title, link, summary, date, author → `app/Models/Post.php`
- [ ] [T007] [P1] Implement getFeedItems() static method for feed query → `app/Models/Post.php`
- [ ] [T008] [P1] Set feed item limit to 20 posts → `app/Models/Post.php`

---

## Phase 3: US1 – Subscribe to Main Blog Feed (P1) 🎯 MVP

- [ ] [T009] [P1] [US1] Verify main feed route /feed is registered → `routes/web.php`
- [ ] [T010] [P1] [US1] Configure feed metadata (title, description, language) → `config/feed.php`
- [ ] [T011] [P1] [US1] Include author name and email in feed items → `app/Models/Post.php`
- [ ] [T012] [P1] [US1] Include categories as feed item categories → `app/Models/Post.php`
- [ ] [T013] [P1] [US1] Include tags as feed item categories → `app/Models/Post.php`
- [ ] [T014] [P1] [US1] Set correct Content-Type header (application/rss+xml) → `config/feed.php`
- [ ] [T015] [P1] [US1] Exclude draft and private posts from feed → `app/Models/Post.php`
- [ ] [T016] [P1] [US1] Write tests for main feed validity and content → `tests/Feature/RssFeedTest.php`

---

## Phase 4: US2 – Subscribe to Category Feed (P2)

- [ ] [T017] [P2] [US2] Create FeedController → `app/Http/Controllers/FeedController.php`
- [ ] [T018] [P2] [US2] Implement category() method for category-specific feed → `app/Http/Controllers/FeedController.php`
- [ ] [T019] [P2] [US2] Register route GET /category/{slug}/feed → `routes/web.php`
- [ ] [T020] [P2] [US2] Set category name in feed title → `app/Http/Controllers/FeedController.php`
- [ ] [T021] [P2] [US2] Return 404 for non-existent category → `app/Http/Controllers/FeedController.php`
- [ ] [T022] [P2] [US2] Write tests for category feed → `tests/Feature/RssCategoryFeedTest.php`

---

## Phase 5: US3 – Subscribe to Author Feed (P3)

- [ ] [T023] [P3] [US3] Implement author() method for author-specific feed → `app/Http/Controllers/FeedController.php`
- [ ] [T024] [P3] [US3] Register route GET /author/{username}/feed → `routes/web.php` (matches 007-public-frontend author route)
- [ ] [T025] [P3] [US3] Set author name in feed title → `app/Http/Controllers/FeedController.php`
- [ ] [T026] [P3] [US3] Return 404 for non-existent author → `app/Http/Controllers/FeedController.php`
- [ ] [T027] [P3] [US3] Write tests for author feed → `tests/Feature/RssAuthorFeedTest.php`

---

## Phase 6: US4 – Discover Feed URLs (P2)

- [ ] [T028] [P2] [US4] Add RSS auto-discovery link tag to blog layout head → `resources/views/layouts/blog.blade.php`
- [ ] [T029] [P2] [US4] Add category-specific RSS link on category pages → `resources/views/categories/show.blade.php`
- [ ] [T030] [P2] [US4] Add visible RSS icon/link in header or footer → `resources/views/layouts/blog.blade.php`
- [ ] [T031] [P2] [US4] Write tests for auto-discovery link presence → `tests/Feature/RssDiscoveryTest.php`

---

## Phase 7: Feed Content Enhancements

- [ ] [T032] [P2] Include featured image as media enclosure → `app/Models/Post.php`
- [ ] [T033] [P2] Use excerpt if available, truncate content otherwise → `app/Models/Post.php`
- [ ] [T034] [P2] Properly escape special characters in XML → `app/Models/Post.php`
- [ ] [T035] [P3] Include permalink as guid → `app/Models/Post.php`
- [ ] [T036] [P3] Set lastBuildDate to most recent post date → `config/feed.php`

---

## Phase 8: Polish & Cross-Cutting Concerns

- [ ] [T037] [P2] Handle empty feed scenario (no published posts) → `app/Models/Post.php`
- [ ] [T038] [P3] Add Cache-Control header for feed caching → `app/Http/Controllers/FeedController.php`
- [ ] [T039] [P3] Test feed with major RSS readers (Feedly, Inoreader) → Manual testing
- [ ] [T040] [P1] Run full test suite and fix any failures
- [ ] [T041] [P1] Run `vendor/bin/pint --dirty` to fix code style
- [ ] [T042] [P1] Update README or documentation if needed
- [ ] [T043] [P1] Create PR and merge to main branch

---

## Summary

| Phase | Tasks | Priority Focus |
|-------|-------|----------------|
| Setup | 3 | P1 |
| Foundational | 5 | P1 |
| US1 – Main Feed | 8 | P1 🎯 MVP |
| US2 – Category Feed | 6 | P2 |
| US3 – Author Feed | 5 | P3 |
| US4 – Discovery | 4 | P2 |
| Content Enhancements | 5 | P2-P3 |
| Polish | 7 | Mixed |

**Total Tasks: 43**
