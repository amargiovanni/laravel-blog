# Tasks: Laravel Blog Engine

**Input**: Design documents from `/specs/001-blog-engine/`
**Prerequisites**: plan.md, spec.md, research.md, data-model.md, contracts/
**Constitution**: Test-First Development is NON-NEGOTIABLE per constitution

**Organization**: Tasks are grouped by user story to enable independent implementation and testing of each story.

## Format: `[ID] [P?] [Story] Description`

- **[P]**: Can run in parallel (different files, no dependencies)
- **[Story]**: Which user story this task belongs to (e.g., US1, US2, US3)
- Include exact file paths in descriptions

## Path Conventions (Laravel)

- **Models**: `app/Models/`
- **Filament Resources**: `app/Filament/Resources/`
- **Livewire/Volt Pages**: `resources/views/livewire/pages/`
- **Tests**: `tests/Feature/`, `tests/Unit/`, `tests/Browser/`
- **Migrations**: `database/migrations/`
- **Factories**: `database/factories/`

---

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Project initialization, package installation, and basic structure

- [ ] T001 Install additional Composer packages: `spatie/laravel-permission`, `spatie/laravel-activitylog`, `spatie/laravel-sitemap`, `spatie/laravel-feed`, `intervention/image-laravel`
- [ ] T002 [P] Create blog configuration file in `config/blog.php`
- [ ] T003 [P] Configure TailwindCSS v4 dark mode in `resources/css/app.css`
- [ ] T004 [P] Setup Alpine.js theme persistence component in `resources/js/app.js`
- [ ] T005 Publish and configure Spatie Permission config in `config/permission.php`
- [ ] T006 Publish and configure Spatie Activitylog config in `config/activitylog.php`
- [ ] T007 [P] Create main layout component in `resources/views/components/layouts/app.blade.php`
- [ ] T008 Configure Filament panel provider in `app/Providers/Filament/AdminPanelProvider.php`

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Core database schema, models, and authentication that MUST be complete before ANY user story

**⚠️ CRITICAL**: No user story work can begin until this phase is complete

### Database Migrations

- [ ] T009 Create users table migration with avatar, bio, theme_preference fields in `database/migrations/`
- [ ] T010 [P] Create categories table migration with parent_id self-reference in `database/migrations/`
- [ ] T011 [P] Create tags table migration in `database/migrations/`
- [ ] T012 [P] Create media table migration in `database/migrations/`
- [ ] T013 Create posts table migration with all fields per data-model.md in `database/migrations/`
- [ ] T014 [P] Create category_post pivot table migration in `database/migrations/`
- [ ] T015 [P] Create post_tag pivot table migration in `database/migrations/`
- [ ] T016 Create comments table migration with parent_id threading in `database/migrations/`
- [ ] T017 [P] Create settings table migration in `database/migrations/`
- [ ] T018 Run Spatie Permission migrations
- [ ] T019 Run Spatie Activitylog migrations
- [ ] T020 Run all migrations and verify schema

### Base Models (shared across stories)

- [ ] T021 [P] Update User model with relationships and Spatie traits in `app/Models/User.php`
- [ ] T022 [P] Create Setting model with helpers in `app/Models/Setting.php`
- [ ] T023 [P] Create Media model with size generation methods in `app/Models/Media.php`

### Factories & Seeders

- [ ] T024 [P] Create UserFactory with role states in `database/factories/UserFactory.php`
- [ ] T025 Create RolesAndPermissionsSeeder with admin/editor/author/subscriber in `database/seeders/`
- [ ] T026 Create AdminUserSeeder for default admin account in `database/seeders/`
- [ ] T027 Update DatabaseSeeder to call role and admin seeders in `database/seeders/DatabaseSeeder.php`
- [ ] T028 Run seeders and verify roles/permissions work

### Services (shared infrastructure)

- [ ] T029 [P] Create ImageService for image processing in `app/Services/ImageService.php`

**Checkpoint**: Foundation ready - user story implementation can now begin

---

## Phase 3: User Story 7 - Authentication (Priority: P1) 🎯 MVP

**Goal**: User registration, login, logout, password reset, and email verification

**Independent Test**: Register new user, verify email, login, logout, reset password

**Why first**: Authentication is required for all author/admin functionality (US1 depends on this)

### Tests for User Story 7

- [ ] T030 [P] [US7] Create registration test in `tests/Feature/Auth/RegistrationTest.php`
- [ ] T031 [P] [US7] Create login test in `tests/Feature/Auth/LoginTest.php`
- [ ] T032 [P] [US7] Create password reset test in `tests/Feature/Auth/PasswordResetTest.php`
- [ ] T033 [P] [US7] Create email verification test in `tests/Feature/Auth/EmailVerificationTest.php`

### Implementation for User Story 7

- [ ] T034 [P] [US7] Create login Volt page in `resources/views/livewire/pages/auth/login.blade.php`
- [ ] T035 [P] [US7] Create register Volt page in `resources/views/livewire/pages/auth/register.blade.php`
- [ ] T036 [P] [US7] Create forgot-password Volt page in `resources/views/livewire/pages/auth/forgot-password.blade.php`
- [ ] T037 [P] [US7] Create reset-password Volt page in `resources/views/livewire/pages/auth/reset-password.blade.php`
- [ ] T038 [P] [US7] Create verify-email Volt page in `resources/views/livewire/pages/auth/verify-email.blade.php`
- [ ] T039 [US7] Create LogoutController in `app/Http/Controllers/Auth/LogoutController.php`
- [ ] T040 [US7] Create VerifyEmailController in `app/Http/Controllers/Auth/VerifyEmailController.php`
- [ ] T041 [US7] Configure auth routes in `routes/web.php`
- [ ] T042 [US7] Run auth tests and verify all pass

**Checkpoint**: Users can register, login, logout, and reset passwords

---

## Phase 4: User Story 1 - Author Creates Posts (Priority: P1) 🎯 MVP

**Goal**: Authors can create, edit, save drafts, schedule, and publish blog posts via admin

**Independent Test**: Create post, save draft, edit, publish, verify visible on frontend

### Tests for User Story 1

- [ ] T043 [P] [US1] Create Post model unit test in `tests/Unit/Models/PostTest.php`
- [ ] T044 [P] [US1] Create PostResource feature test in `tests/Feature/Admin/PostResourceTest.php`
- [ ] T045 [P] [US1] Create post publishing test in `tests/Feature/Post/PublishingTest.php`

### Implementation for User Story 1

- [ ] T046 [P] [US1] Create Post model with relationships and scopes in `app/Models/Post.php`
- [ ] T047 [P] [US1] Create PostFactory with status states in `database/factories/PostFactory.php`
- [ ] T048 [US1] Create PostPolicy for authorization in `app/Policies/PostPolicy.php`
- [ ] T049 [US1] Create PostObserver for activity logging and slug generation in `app/Observers/PostObserver.php`
- [ ] T050 [US1] Create PostResource with full CRUD in `app/Filament/Resources/PostResource.php`
- [ ] T051 [US1] Create PostResource/Pages/CreatePost.php
- [ ] T052 [US1] Create PostResource/Pages/EditPost.php
- [ ] T053 [US1] Create PostResource/Pages/ListPosts.php
- [ ] T054 [US1] Create scheduled post publisher command in `app/Console/Commands/PublishScheduledPosts.php`
- [ ] T055 [US1] Register scheduler for post publishing in `routes/console.php`
- [ ] T055b [US1] Implement auto-save for post drafts using Livewire wire:poll.30s in PostResource form
- [ ] T056 [US1] Run post tests and verify all pass

**Checkpoint**: Authors can create and publish posts via admin panel

---

## Phase 5: User Story 2 - Reader Browses Posts (Priority: P1) 🎯 MVP

**Goal**: Public visitors can browse homepage, post list, and read individual posts

**Independent Test**: Visit homepage, see posts, click post, read content, see pagination

### Tests for User Story 2

- [ ] T057 [P] [US2] Create homepage test in `tests/Feature/Post/HomepageTest.php`
- [ ] T058 [P] [US2] Create post listing test in `tests/Feature/Post/ListingTest.php`
- [ ] T059 [P] [US2] Create single post test in `tests/Feature/Post/ShowTest.php`

### Implementation for User Story 2

- [ ] T060 [P] [US2] Create home Volt page in `resources/views/livewire/pages/home.blade.php`
- [ ] T061 [P] [US2] Create post index Volt page in `resources/views/livewire/pages/post/index.blade.php`
- [ ] T062 [P] [US2] Create post show Volt page in `resources/views/livewire/pages/post/show.blade.php`
- [ ] T063 [US2] Create post card component in `resources/views/components/post-card.blade.php`
- [ ] T064 [US2] Create pagination component in `resources/views/components/pagination.blade.php`
- [ ] T065 [US2] Configure public post routes in `routes/web.php`
- [ ] T066 [US2] Run post display tests and verify all pass

**Checkpoint**: Readers can browse and read published posts - MVP COMPLETE! 🎉

---

## Phase 6: User Story 3 - Categories (Priority: P2)

**Goal**: Authors can create hierarchical categories, assign to posts, readers browse by category

**Independent Test**: Create category with parent, assign to post, view category page with posts

### Tests for User Story 3

- [ ] T067 [P] [US3] Create Category model test in `tests/Unit/Models/CategoryTest.php`
- [ ] T068 [P] [US3] Create CategoryResource test in `tests/Feature/Admin/CategoryResourceTest.php`
- [ ] T069 [P] [US3] Create category page test in `tests/Feature/Category/ShowTest.php`

### Implementation for User Story 3

- [ ] T070 [P] [US3] Create Category model with parent/children relationships in `app/Models/Category.php`
- [ ] T071 [P] [US3] Create CategoryFactory in `database/factories/CategoryFactory.php`
- [ ] T072 [US3] Create CategoryPolicy in `app/Policies/CategoryPolicy.php`
- [ ] T073 [US3] Create CategoryResource with hierarchy support in `app/Filament/Resources/CategoryResource.php`
- [ ] T074 [US3] Add categories field to PostResource form
- [ ] T075 [US3] Create category show Volt page in `resources/views/livewire/pages/category/show.blade.php`
- [ ] T076 [US3] Configure category routes in `routes/web.php`
- [ ] T077 [US3] Add category navigation component in `resources/views/components/category-nav.blade.php`
- [ ] T078 [US3] Run category tests and verify all pass

**Checkpoint**: Categories work independently with full CRUD and browsing

---

## Phase 7: User Story 4 - Tags (Priority: P2)

**Goal**: Authors can add tags to posts with autocomplete, readers browse by tag

**Independent Test**: Add tags to post, autocomplete works, click tag, see tagged posts

### Tests for User Story 4

- [ ] T079 [P] [US4] Create Tag model test in `tests/Unit/Models/TagTest.php`
- [ ] T080 [P] [US4] Create TagResource test in `tests/Feature/Admin/TagResourceTest.php`
- [ ] T081 [P] [US4] Create tag page test in `tests/Feature/Tag/ShowTest.php`

### Implementation for User Story 4

- [ ] T082 [P] [US4] Create Tag model with post relationship in `app/Models/Tag.php`
- [ ] T083 [P] [US4] Create TagFactory in `database/factories/TagFactory.php`
- [ ] T084 [US4] Create TagResource in `app/Filament/Resources/TagResource.php`
- [ ] T085 [US4] Add tags field with autocomplete to PostResource form
- [ ] T086 [US4] Create tag show Volt page in `resources/views/livewire/pages/tag/show.blade.php`
- [ ] T087 [US4] Configure tag routes in `routes/web.php`
- [ ] T088 [US4] Display tags on post show page
- [ ] T089 [US4] Run tag tests and verify all pass

**Checkpoint**: Tags work independently with full CRUD and browsing

---

## Phase 8: User Story 9 - Media Library (Priority: P2)

**Goal**: Authors can upload, manage, and insert images into posts

**Independent Test**: Upload image, see optimized versions, insert into post, view in media library

### Tests for User Story 9

- [ ] T090 [P] [US9] Create Media model test in `tests/Unit/Models/MediaTest.php`
- [ ] T091 [P] [US9] Create ImageService test in `tests/Unit/Services/ImageServiceTest.php`
- [ ] T092 [P] [US9] Create MediaResource test in `tests/Feature/Admin/MediaResourceTest.php`

### Implementation for User Story 9

- [ ] T093 [US9] Update Media model with optimization methods in `app/Models/Media.php`
- [ ] T094 [P] [US9] Create MediaFactory in `database/factories/MediaFactory.php`
- [ ] T095 [US9] Create MediaPolicy in `app/Policies/MediaPolicy.php`
- [ ] T096 [US9] Update ImageService with resize/optimize/WebP conversion in `app/Services/ImageService.php`
- [ ] T097 [US9] Create MediaResource with gallery view in `app/Filament/Resources/MediaResource.php`
- [ ] T098 [US9] Create MediaObserver for cleanup on delete in `app/Observers/MediaObserver.php`
- [ ] T099 [US9] Integrate media picker in PostResource featured image field
- [ ] T100 [US9] Configure storage disk and symlink
- [ ] T101 [US9] Run media tests and verify all pass

**Checkpoint**: Media library works with image optimization

---

## Phase 9: User Story 5 - Comments (Priority: P2)

**Goal**: Readers can comment on posts with threading, spam protection

**Independent Test**: Submit comment, see in moderation queue, guest vs logged-in flow

### Tests for User Story 5

- [ ] T102 [P] [US5] Create Comment model test in `tests/Unit/Models/CommentTest.php`
- [ ] T103 [P] [US5] Create comment submission test in `tests/Feature/Comment/SubmissionTest.php`
- [ ] T104 [P] [US5] Create spam protection test in `tests/Feature/Comment/SpamProtectionTest.php`

### Implementation for User Story 5

- [ ] T105 [P] [US5] Create Comment model with threading in `app/Models/Comment.php`
- [ ] T106 [P] [US5] Create CommentFactory in `database/factories/CommentFactory.php`
- [ ] T107 [US5] Create CommentPolicy in `app/Policies/CommentPolicy.php`
- [ ] T108 [US5] Create SpamProtectionService in `app/Services/SpamProtectionService.php`
- [ ] T109 [US5] Create Comments Livewire component in `app/Livewire/Comments.php`
- [ ] T110 [US5] Create comment form component in `resources/views/livewire/comments.blade.php`
- [ ] T111 [US5] Add comments section to post show page
- [ ] T112 [US5] Implement rate limiting middleware for comments
- [ ] T113 [US5] Run comment tests and verify all pass

**Checkpoint**: Comments work with spam protection

---

## Phase 10: User Story 6 - Comment Moderation (Priority: P2)

**Goal**: Admins can moderate comments via Filament panel

**Independent Test**: View pending queue, approve/reject comments, bulk actions

### Tests for User Story 6

- [ ] T114 [P] [US6] Create CommentResource test in `tests/Feature/Admin/CommentResourceTest.php`
- [ ] T115 [P] [US6] Create moderation workflow test in `tests/Feature/Comment/ModerationTest.php`

### Implementation for User Story 6

- [ ] T116 [US6] Create CommentResource with moderation actions in `app/Filament/Resources/CommentResource.php`
- [ ] T117 [US6] Add bulk approve/reject/delete actions to CommentResource
- [ ] T118 [US6] Create PendingCommentsWidget for dashboard in `app/Filament/Widgets/PendingCommentsWidget.php`
- [ ] T119 [US6] Add notification for new pending comments
- [ ] T120 [US6] Run moderation tests and verify all pass

**Checkpoint**: Comment moderation works in admin panel

---

## Phase 11: User Story 12 - Dashboard Analytics (Priority: P2)

**Goal**: Admin dashboard shows content statistics, trends, and recent activity

**Independent Test**: View dashboard, see stats widgets, verify data accuracy

### Tests for User Story 12

- [ ] T121 [P] [US12] Create dashboard widgets test in `tests/Feature/Admin/DashboardTest.php`

### Implementation for User Story 12

- [ ] T122 [P] [US12] Create StatsOverviewWidget in `app/Filament/Widgets/StatsOverviewWidget.php`
- [ ] T123 [P] [US12] Create RecentActivityWidget in `app/Filament/Widgets/RecentActivityWidget.php`
- [ ] T124 [P] [US12] Create TrendChartWidget in `app/Filament/Widgets/TrendChartWidget.php`
- [ ] T125 [P] [US12] Create LatestPostsWidget in `app/Filament/Widgets/LatestPostsWidget.php`
- [ ] T126 [US12] Configure custom dashboard page in `app/Filament/Pages/Dashboard.php`
- [ ] T127 [US12] Register widgets in AdminPanelProvider
- [ ] T128 [US12] Run dashboard tests and verify all pass

**Checkpoint**: Dashboard shows accurate analytics

---

## Phase 12: User Story 14 - Search (Priority: P2)

**Goal**: Readers can search posts with filtering and autocomplete

**Independent Test**: Search term, see results, filter by category, autocomplete suggestions

### Tests for User Story 14

- [ ] T129 [P] [US14] Create SearchService test in `tests/Unit/Services/SearchServiceTest.php`
- [ ] T130 [P] [US14] Create search page test in `tests/Feature/Search/SearchTest.php`
- [ ] T131 [P] [US14] Create autocomplete test in `tests/Feature/Search/AutocompleteTest.php`

### Implementation for User Story 14

- [ ] T132 [US14] Create SearchService with full-text search in `app/Services/SearchService.php`
- [ ] T133 [US14] Add FULLTEXT index migration for posts table
- [ ] T134 [US14] Create search Volt page in `resources/views/livewire/pages/search.blade.php`
- [ ] T135 [US14] Create SearchController for autocomplete JSON in `app/Http/Controllers/SearchController.php`
- [ ] T136 [US14] Add search input component to header in `resources/views/components/search-input.blade.php`
- [ ] T137 [US14] Configure search routes in `routes/web.php`
- [ ] T138 [US14] Run search tests and verify all pass

**Checkpoint**: Search works with filters and autocomplete

---

## Phase 13: User Story 15 - Dark Mode (Priority: P2)

**Goal**: Readers can toggle dark mode with preference persistence

**Independent Test**: Toggle theme, verify UI adapts, refresh and preference persists

### Tests for User Story 15

- [ ] T139 [P] [US15] Create dark mode browser test in `tests/Browser/DarkModeTest.php`

### Implementation for User Story 15

- [ ] T140 [US15] Create ThemeToggle Livewire component in `app/Livewire/ThemeToggle.php`
- [ ] T141 [US15] Create theme toggle view in `resources/views/livewire/theme-toggle.blade.php`
- [ ] T142 [US15] Update CSS for dark mode variants in `resources/css/app.css`
- [ ] T143 [US15] Add theme toggle to main layout header
- [ ] T144 [US15] Ensure all components support dark: variants
- [ ] T145 [US15] Run dark mode test and verify pass

**Checkpoint**: Dark mode works with persistence

---

## Phase 14: User Story 8 - User & Role Management (Priority: P3)

**Goal**: Admin can manage users and assign roles

**Independent Test**: Create user, assign role, verify permission restrictions

### Tests for User Story 8

- [ ] T146 [P] [US8] Create UserResource test in `tests/Feature/Admin/UserResourceTest.php`
- [ ] T147 [P] [US8] Create role permission test in `tests/Feature/Auth/RolePermissionTest.php`

### Implementation for User Story 8

- [ ] T148 [US8] Create UserPolicy in `app/Policies/UserPolicy.php`
- [ ] T149 [US8] Create UserResource with role management in `app/Filament/Resources/UserResource.php`
- [ ] T150 [US8] Add role-based navigation visibility in AdminPanelProvider
- [ ] T151 [US8] Run user management tests and verify all pass

**Checkpoint**: User and role management works

---

## Phase 15: User Story 10 - SEO (Priority: P3)

**Goal**: Posts have proper meta tags, Open Graph, sitemap, and robots.txt

**Independent Test**: Check post meta tags, share preview, validate sitemap

### Tests for User Story 10

- [ ] T152 [P] [US10] Create SEO meta test in `tests/Feature/Seo/MetaTagsTest.php`
- [ ] T153 [P] [US10] Create sitemap test in `tests/Feature/Seo/SitemapTest.php`

### Implementation for User Story 10

- [ ] T154 [US10] Create HasSeoMeta trait for Post model in `app/Traits/HasSeoMeta.php`
- [ ] T155 [US10] Create SitemapService in `app/Services/SitemapService.php`
- [ ] T156 [US10] Create SitemapController in `app/Http/Controllers/SitemapController.php`
- [ ] T157 [US10] Create SEO component for head in `resources/views/components/seo-meta.blade.php`
- [ ] T158 [US10] Add SEO fields section to PostResource form
- [ ] T159 [US10] Configure sitemap route and scheduled regeneration
- [ ] T160 [US10] Run SEO tests and verify all pass

**Checkpoint**: SEO works with sitemap generation

---

## Phase 16: User Story 11 - RSS Feeds (Priority: P3)

**Goal**: Blog provides RSS/Atom feeds for posts and categories

**Independent Test**: Access /feed, validate RSS XML, check category feed

### Tests for User Story 11

- [ ] T161 [P] [US11] Create RSS feed test in `tests/Feature/Feed/RssFeedTest.php`

### Implementation for User Story 11

- [ ] T162 [US11] Configure spatie/laravel-feed in `config/feed.php`
- [ ] T163 [US11] Add Feedable interface to Post model
- [ ] T164 [US11] Create FeedController for category feeds in `app/Http/Controllers/FeedController.php`
- [ ] T165 [US11] Configure feed routes in `routes/web.php`
- [ ] T166 [US11] Add RSS link to layout head
- [ ] T167 [US11] Run feed tests and verify all pass

**Checkpoint**: RSS feeds work and validate

---

## Phase 17: User Story 13 - Activity Log (Priority: P3)

**Goal**: Admin can view searchable activity log with filtering

**Independent Test**: Perform actions, view activity log, filter by user/type

### Tests for User Story 13

- [ ] T168 [P] [US13] Create activity logging test in `tests/Feature/Admin/ActivityLogTest.php`

### Implementation for User Story 13

- [ ] T169 [US13] Add LogsActivity trait to Post, Comment, User models
- [ ] T170 [US13] Create ActivityLogPage in `app/Filament/Pages/ActivityLogPage.php`
- [ ] T171 [US13] Add activity log cleanup command to scheduler
- [ ] T172 [US13] Run activity log tests and verify all pass

**Checkpoint**: Activity log works with filtering

---

## Phase 18: User Story 16 - Social Sharing (Priority: P3)

**Goal**: Posts have social share buttons for major platforms

**Independent Test**: Click share button, verify prefilled content, copy link works

### Tests for User Story 16

- [ ] T173 [P] [US16] Create social share test in `tests/Feature/Social/ShareTest.php`

### Implementation for User Story 16

- [ ] T174 [US16] Create SocialShare component in `resources/views/components/social-share.blade.php`
- [ ] T175 [US16] Add social share buttons to post show page
- [ ] T176 [US16] Implement copy-to-clipboard with Alpine.js
- [ ] T177 [US16] Run social share tests and verify all pass

**Checkpoint**: Social sharing works

---

## Phase 19: User Story 17 - Themes (Priority: P3)

**Goal**: Admin can switch between frontend themes

**Independent Test**: Select theme, preview, activate, verify frontend changes

### Tests for User Story 17

- [ ] T178 [P] [US17] Create theme switching test in `tests/Feature/Theme/ThemeSwitchTest.php`

### Implementation for User Story 17

- [ ] T179 [US17] Create ThemeService in `app/Services/ThemeService.php`
- [ ] T180 [US17] Create ThemeServiceProvider in `app/Providers/ThemeServiceProvider.php`
- [ ] T181 [US17] Create ThemeSettingsPage in `app/Filament/Pages/ThemeSettingsPage.php`
- [ ] T182 [US17] Create default theme directory structure in `resources/views/themes/default/`
- [ ] T183 [US17] Add theme setting to settings table
- [ ] T184 [US17] Run theme tests and verify all pass

**Checkpoint**: Theme switching works

---

## Phase 20: Polish & Cross-Cutting Concerns

**Purpose**: Final improvements affecting multiple user stories

- [ ] T185 [P] Create SettingsPage in `app/Filament/Pages/SettingsPage.php`
- [ ] T186 [P] Add comprehensive seeders for demo data in `database/seeders/DemoDataSeeder.php`
- [ ] T187 Run `vendor/bin/pint` and fix all formatting issues
- [ ] T188 Run `vendor/bin/phpstan analyse` and fix all static analysis issues
- [ ] T189 Run full test suite `php artisan test` and ensure all pass
- [ ] T190 Run `npm run build` and verify assets compile
- [ ] T191 Validate quickstart.md instructions work on fresh clone
- [ ] T192 Performance review: ensure <2s page load, <1s search

---

## Dependencies & Execution Order

### Phase Dependencies

- **Setup (Phase 1)**: No dependencies - can start immediately
- **Foundational (Phase 2)**: Depends on Setup completion - BLOCKS all user stories
- **US7 Auth (Phase 3)**: First user story - required for admin access
- **US1 Post Creation (Phase 4)**: Requires US7 (auth)
- **US2 Post Display (Phase 5)**: Requires US1 (posts exist) - MVP COMPLETE after this
- **US3-US17 (Phases 6-19)**: Can proceed in priority order or parallel
- **Polish (Phase 20)**: Depends on all desired user stories being complete

### User Story Dependencies

| Story | Priority | Depends On | Can Parallel With |
|-------|----------|------------|-------------------|
| US7 Authentication | P1 | Foundational | - |
| US1 Post Creation | P1 | US7 | - |
| US2 Post Display | P1 | US1 | - |
| US3 Categories | P2 | US1 | US4, US9 |
| US4 Tags | P2 | US1 | US3, US9 |
| US5 Comments | P2 | US2 | US6 |
| US6 Moderation | P2 | US5 | - |
| US9 Media | P2 | US1 | US3, US4 |
| US12 Dashboard | P2 | US1 | US14, US15 |
| US14 Search | P2 | US2 | US12, US15 |
| US15 Dark Mode | P2 | Foundational | US12, US14 |
| US8 User Mgmt | P3 | US7 | US10, US11 |
| US10 SEO | P3 | US2 | US8, US11 |
| US11 RSS | P3 | US2 | US8, US10 |
| US13 Activity Log | P3 | US1 | US16, US17 |
| US16 Social Share | P3 | US2 | US13, US17 |
| US17 Themes | P3 | Foundational | US13, US16 |

---

## Parallel Example: Phase 6-9 (P2 Stories)

After MVP complete, these can run in parallel:

```bash
# Developer A: Categories
- [ ] T070-T078 (US3)

# Developer B: Tags
- [ ] T082-T089 (US4)

# Developer C: Media Library
- [ ] T093-T101 (US9)

# Developer D: Comments
- [ ] T105-T113 (US5)
```

---

## Implementation Strategy

### MVP First (Phases 1-5)

1. Complete Phase 1: Setup
2. Complete Phase 2: Foundational (CRITICAL)
3. Complete Phase 3: US7 Authentication
4. Complete Phase 4: US1 Post Creation
5. Complete Phase 5: US2 Post Display
6. **STOP and VALIDATE**: Test full post lifecycle
7. **MVP READY**: Blog can create and display posts

### Incremental Delivery

1. MVP (US7 + US1 + US2) → Deploy
2. Add Categories + Tags (US3 + US4) → Deploy
3. Add Comments + Moderation (US5 + US6) → Deploy
4. Add Media Library (US9) → Deploy
5. Add Search + Dashboard (US12 + US14) → Deploy
6. Add P3 features (US8, US10, US11, US13, US16, US17) → Deploy

---

## Summary

| Metric | Value |
|--------|-------|
| **Total Tasks** | 192 |
| **Total User Stories** | 17 |
| **MVP Tasks** | T001-T066 (66 tasks) |
| **P1 Stories** | US1, US2, US7 |
| **P2 Stories** | US3, US4, US5, US6, US9, US12, US14, US15 |
| **P3 Stories** | US8, US10, US11, US13, US16, US17 |

---

## Notes

- [P] tasks = different files, no dependencies
- [Story] label maps task to specific user story for traceability
- Constitution requires Test-First: write tests FIRST, ensure they FAIL
- Commit after each task or logical group
- Run `vendor/bin/pint --dirty` before each commit
- Stop at any checkpoint to validate story independently
