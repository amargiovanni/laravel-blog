# Tasks – 009-xml-sitemap

> Generated from spec.md, plan.md, and design artifacts.

---

## Phase 1: Setup

- [ ] [T001] [P1] Create feature branch `009-xml-sitemap` from main (if not exists)
- [ ] [T002] [P1] Install spatie/laravel-sitemap package → `composer require spatie/laravel-sitemap`
- [ ] [T003] [P1] Verify package is installed and functional

---

## Phase 2: Foundational

- [ ] [T004] [P1] Create SitemapController → `app/Http/Controllers/SitemapController.php`
- [ ] [T005] [P1] Create SitemapService for sitemap generation logic → `app/Services/SitemapService.php`
- [ ] [T006] [P1] Register route GET /sitemap.xml → `routes/web.php`
- [ ] [T007] [P1] Configure sitemap generation with Spatie package → `app/Services/SitemapService.php`

---

## Phase 3: US1 – Generate XML Sitemap Automatically (P1) 🎯 MVP

- [ ] [T008] [P1] [US1] Implement index() method in SitemapController → `app/Http/Controllers/SitemapController.php`
- [ ] [T009] [P1] [US1] Add homepage URL with priority 1.0, changefreq daily → `app/Services/SitemapService.php`
- [ ] [T010] [P1] [US1] Add all published posts to sitemap with proper lastmod → `app/Services/SitemapService.php`
- [ ] [T011] [P1] [US1] Exclude draft and private posts from sitemap → `app/Services/SitemapService.php`
- [ ] [T012] [P1] [US1] Return valid XML response with correct content type → `app/Http/Controllers/SitemapController.php`
- [ ] [T013] [P1] [US1] Write tests for sitemap XML validity → `tests/Feature/SitemapTest.php`
- [ ] [T014] [P1] [US1] Write tests for post inclusion/exclusion → `tests/Feature/SitemapTest.php`

---

## Phase 4: US2 – Include All Content Types (P1) 🎯 MVP

- [ ] [T015] [P1] [US2] Add all published pages to sitemap → `app/Services/SitemapService.php`
- [ ] [T016] [P1] [US2] Add category archive URLs (categories with posts only) → `app/Services/SitemapService.php`
- [ ] [T017] [P1] [US2] Add tag archive URLs (tags with posts only) → `app/Services/SitemapService.php`
- [ ] [T018] [P1] [US2] Add author page URLs (authors with published posts) → `app/Services/SitemapService.php`
- [ ] [T019] [P1] [US2] Add blog index page URL → `app/Services/SitemapService.php`
- [ ] [T020] [P1] [US2] Write tests for page, category, tag, author inclusion → `tests/Feature/SitemapContentTypesTest.php`

---

## Phase 5: US3 – Include Sitemap Metadata (P2)

- [ ] [T021] [P2] [US3] Add lastmod date for each entry from updated_at → `app/Services/SitemapService.php`
- [ ] [T022] [P2] [US3] Add changefreq values based on content type → `app/Services/SitemapService.php`
- [ ] [T023] [P2] [US3] Implement priority matrix (homepage 1.0, posts 0.8, pages 0.7, archives 0.5-0.6) → `app/Services/SitemapService.php`
- [ ] [T024] [P2] [US3] Write tests for metadata presence and values → `tests/Feature/SitemapMetadataTest.php`

---

## Phase 6: US4 – Submit Sitemap to Search Engines (P3)

- [ ] [T025] [P3] [US4] Create robots() method in SitemapController → `app/Http/Controllers/SitemapController.php`
- [ ] [T026] [P3] [US4] Register route GET /robots.txt → `routes/web.php`
- [ ] [T027] [P3] [US4] Include sitemap URL in robots.txt response → `app/Http/Controllers/SitemapController.php`
- [ ] [T028] [P3] [US4] Implement sitemap index for sites with > 50,000 URLs → `app/Services/SitemapService.php`
- [ ] [T029] [P3] [US4] Create separate sitemap files (posts, pages, categories, tags, authors) → `app/Services/SitemapService.php`
- [ ] [T030] [P3] [US4] Write tests for robots.txt and sitemap index → `tests/Feature/SitemapIndexTest.php`

---

## Phase 7: Image Sitemap Support

- [ ] [T031] [P2] Add featured images to post sitemap entries → `app/Services/SitemapService.php`
- [ ] [T032] [P2] Include image title in image sitemap extension → `app/Services/SitemapService.php`
- [ ] [T033] [P2] Write tests for image sitemap entries → `tests/Feature/SitemapImagesTest.php`

---

## Phase 8: Artisan Command & Caching

- [ ] [T034] [P2] Create GenerateSitemap Artisan command → `app/Console/Commands/GenerateSitemap.php`
- [ ] [T035] [P2] Implement sitemap writing to public folder → `app/Console/Commands/GenerateSitemap.php`
- [ ] [T036] [P2] Add command output with generation stats → `app/Console/Commands/GenerateSitemap.php`
- [ ] [T037] [P3] Implement cache invalidation on content publish/update events → `app/Services/SitemapService.php`
- [ ] [T038] [P3] Add --index option to force sitemap index generation → `app/Console/Commands/GenerateSitemap.php`

---

## Phase 9: Polish & Cross-Cutting Concerns

- [ ] [T039] [P2] Properly encode URLs with special characters → `app/Services/SitemapService.php`
- [ ] [T040] [P3] Add response caching for sitemap route → `app/Http/Controllers/SitemapController.php`
- [ ] [T041] [P3] Handle edge case: no published content (empty sitemap) → `app/Services/SitemapService.php`
- [ ] [T042] [P1] Run full test suite and fix any failures
- [ ] [T043] [P1] Run `vendor/bin/pint --dirty` to fix code style
- [ ] [T044] [P1] Update README or documentation if needed
- [ ] [T045] [P1] Create PR and merge to main branch

---

## Summary

| Phase | Tasks | Priority Focus |
|-------|-------|----------------|
| Setup | 3 | P1 |
| Foundational | 4 | P1 |
| US1 – Generate Sitemap | 7 | P1 🎯 MVP |
| US2 – Content Types | 6 | P1 🎯 MVP |
| US3 – Metadata | 4 | P2 |
| US4 – Search Engines | 6 | P3 |
| Image Support | 3 | P2 |
| Artisan & Cache | 5 | P2-P3 |
| Polish | 7 | Mixed |

**Total Tasks: 45**
