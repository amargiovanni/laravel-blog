# Tasks – 011-related-posts

> Generated from spec.md, plan.md, and design artifacts.

---

## Phase 1: Setup

- [ ] [T001] [P1] Create feature branch `011-related-posts` from main (if not exists)
- [ ] [T002] [P1] Verify Laravel Cache driver is configured
- [ ] [T003] [P1] Optional: Create config/blog.php for related posts settings

---

## Phase 2: Foundational – Service Implementation

- [ ] [T004] [P1] Create RelatedPostsService class → `app/Services/RelatedPostsService.php`
- [ ] [T005] [P1] Implement getRelatedPosts(Post $post, int $limit = 4) method → `app/Services/RelatedPostsService.php`
- [ ] [T006] [P1] Implement tag-based matching query → `app/Services/RelatedPostsService.php`
- [ ] [T007] [P1] Implement category-based matching query → `app/Services/RelatedPostsService.php`
- [ ] [T008] [P1] Implement relevance scoring: (shared_tags × 3) + (same_category × 1) → `app/Services/RelatedPostsService.php`
- [ ] [T009] [P1] Add recency bonus calculation to scoring → `app/Services/RelatedPostsService.php`
- [ ] [T010] [P1] Order results by relevance score descending, then by date → `app/Services/RelatedPostsService.php`

---

## Phase 3: US1 – View Related Posts After Reading (P1) 🎯 MVP

- [ ] [T011] [P1] [US1] Add relatedPosts() method to Post model → `app/Models/Post.php`
- [ ] [T012] [P1] [US1] Exclude current post from results → `app/Services/RelatedPostsService.php`
- [ ] [T013] [P1] [US1] Exclude unpublished posts from results → `app/Services/RelatedPostsService.php`
- [ ] [T014] [P1] [US1] Create RelatedPosts Blade component → `app/View/Components/RelatedPosts.php`
- [ ] [T015] [P1] [US1] Create related-posts blade view with grid layout → `resources/views/components/related-posts.blade.php`
- [ ] [T016] [P1] [US1] Display thumbnail, title, and date for each post → `resources/views/components/related-posts.blade.php`
- [ ] [T017] [P1] [US1] Add component to single post view → `resources/views/posts/show.blade.php`
- [ ] [T018] [P1] [US1] Write feature tests for related posts display → `tests/Feature/RelatedPostsTest.php`

---

## Phase 4: US2 – See Relevance-Based Ordering (P2)

- [ ] [T019] [P2] [US2] Weight tag matches higher than category matches → `app/Services/RelatedPostsService.php`
- [ ] [T020] [P2] [US2] Posts with multiple shared tags rank higher → `app/Services/RelatedPostsService.php`
- [ ] [T021] [P2] [US2] Use publication date as tie-breaker for equal scores → `app/Services/RelatedPostsService.php`
- [ ] [T022] [P2] [US2] Write tests for relevance ordering → `tests/Feature/RelatedPostsOrderingTest.php`

---

## Phase 5: US3 – Handle Edge Cases Gracefully (P2)

- [ ] [T023] [P2] [US3] Implement fallback to recent posts when no matches → `app/Services/RelatedPostsService.php`
- [ ] [T024] [P2] [US3] Fill with recent posts when fewer than limit matches exist → `app/Services/RelatedPostsService.php`
- [ ] [T025] [P2] [US3] Hide section when no related posts available → `resources/views/components/related-posts.blade.php`
- [ ] [T026] [P2] [US3] Handle missing featured image with fallback/placeholder → `resources/views/components/related-posts.blade.php`
- [ ] [T027] [P2] [US3] Write tests for edge case handling → `tests/Feature/RelatedPostsEdgeCasesTest.php`

---

## Phase 6: Caching Layer

- [ ] [T028] [P2] Implement cache key generation (related_posts:{post_id}:{limit}) → `app/Services/RelatedPostsService.php`
- [ ] [T029] [P2] Cache results with appropriate TTL (1 hour production) → `app/Services/RelatedPostsService.php`
- [ ] [T030] [P2] Implement clearCache() method → `app/Services/RelatedPostsService.php`
- [ ] [T031] [P2] Add cache invalidation on post save/update → `app/Observers/PostObserver.php`
- [ ] [T032] [P2] Add cache invalidation on post tags change → `app/Observers/PostObserver.php`
- [ ] [T033] [P2] Write tests for cache functionality → `tests/Feature/RelatedPostsCacheTest.php`

---

## Phase 7: Polish & Cross-Cutting Concerns

- [ ] [T034] [P2] Make limit configurable via component parameter → `app/View/Components/RelatedPosts.php`
- [ ] [T035] [P2] Style related posts section with TailwindCSS → `resources/views/components/related-posts.blade.php`
- [ ] [T036] [P2] Implement responsive grid layout (mobile/tablet/desktop) → `resources/views/components/related-posts.blade.php`
- [ ] [T037] [P3] Add dark mode support to related posts section → `resources/views/components/related-posts.blade.php`
- [ ] [T038] [P3] Add hover effects and transitions → `resources/views/components/related-posts.blade.php`
- [ ] [T039] [P1] Run full test suite and fix any failures
- [ ] [T040] [P1] Run `vendor/bin/pint --dirty` to fix code style
- [ ] [T041] [P1] Update README or documentation if needed
- [ ] [T042] [P1] Create PR and merge to main branch

---

## Summary

| Phase | Tasks | Priority Focus |
|-------|-------|----------------|
| Setup | 3 | P1 |
| Foundational | 7 | P1 |
| US1 – View Related Posts | 8 | P1 🎯 MVP |
| US2 – Relevance Ordering | 4 | P2 |
| US3 – Edge Cases | 5 | P2 |
| Caching | 6 | P2 |
| Polish | 9 | Mixed |

**Total Tasks: 42**
