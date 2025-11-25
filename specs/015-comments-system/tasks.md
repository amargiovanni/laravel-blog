# Tasks – 015-comments-system

> Generated from spec.md, plan.md, and design artifacts.

---

## Phase 1: Setup

- [ ] [T001] [P1] Create feature branch `015-comments-system` from main (if not exists)
- [ ] [T002] [P1] Verify Laravel Mail and Notifications are configured
- [ ] [T003] [P1] Create config/comments.php with default settings

---

## Phase 2: Foundational – Database & Models

- [ ] [T004] [P1] Create migration for `comments` table with fields: id, post_id, parent_id, user_id, author_name, author_email, author_url, content, status, ip_address, user_agent, is_notify_replies, approved_at, timestamps → `database/migrations/xxxx_create_comments_table.php`
- [ ] [T005] [P1] Create migration to add comments_enabled and comments_count to posts table → `database/migrations/xxxx_add_comments_fields_to_posts_table.php`
- [ ] [T006] [P1] Create Comment model with fillable, casts, relationships (post, parent, replies, user), scopes (approved, pending, rejected, spam, rootLevel) → `app/Models/Comment.php`
- [ ] [T007] [P1] Add approve(), reject(), markAsSpam() methods to Comment model → `app/Models/Comment.php`
- [ ] [T008] [P1] Add getGravatarUrl(), getDepth(), isReply() helper methods → `app/Models/Comment.php`
- [ ] [T009] [P1] Add comments(), approvedComments(), pendingComments() relationships to Post model → `app/Models/Post.php`
- [ ] [T010] [P1] Add commentsAreEnabled() method to Post model → `app/Models/Post.php`
- [ ] [T011] [P1] Add incrementCommentsCount(), decrementCommentsCount() methods to Post model → `app/Models/Post.php`
- [ ] [T012] [P1] Create CommentFactory → `database/factories/CommentFactory.php`
- [ ] [T013] [P1] Run migrations and verify schema

---

## Phase 3: US1 – Submit a Comment (P1) 🎯 MVP

- [ ] [T014] [P1] [US1] Create CommentForm Livewire component → `app/Livewire/CommentForm.php`
- [ ] [T015] [P1] [US1] Create comment-form blade view with name, email, message fields → `resources/views/livewire/comment-form.blade.php`
- [ ] [T016] [P1] [US1] Implement form validation rules (required fields, email format, content length) → `app/Livewire/CommentForm.php`
- [ ] [T017] [P1] [US1] Display inline validation errors → `resources/views/livewire/comment-form.blade.php`
- [ ] [T018] [P1] [US1] Store comment with pending status on submission → `app/Livewire/CommentForm.php`
- [ ] [T019] [P1] [US1] Capture IP address and user agent → `app/Livewire/CommentForm.php`
- [ ] [T020] [P1] [US1] Show success message with moderation notice → `resources/views/livewire/comment-form.blade.php`
- [ ] [T021] [P1] [US1] Auto-fill name/email for logged-in users → `app/Livewire/CommentForm.php`
- [ ] [T022] [P1] [US1] Create CommentList Livewire component → `app/Livewire/CommentList.php`
- [ ] [T023] [P1] [US1] Create comment-list blade view → `resources/views/livewire/comment-list.blade.php`
- [ ] [T024] [P1] [US1] Display approved comments with author, date, content, avatar → `resources/views/livewire/comment-list.blade.php`
- [ ] [T025] [P1] [US1] Write feature tests for comment submission → `tests/Feature/CommentSubmissionTest.php`

---

## Phase 4: US2 – Moderate Comments (P1) 🎯 MVP

- [ ] [T026] [P1] [US2] Create CommentResource for Filament → `app/Filament/Resources/CommentResource.php`
- [ ] [T027] [P1] [US2] Implement table with author_name, author_email, content (truncated), post, status, created_at columns → `app/Filament/Resources/CommentResource.php`
- [ ] [T028] [P1] [US2] Add filters: status (pending/approved/rejected/spam), post, date range → `app/Filament/Resources/CommentResource.php`
- [ ] [T029] [P1] [US2] Implement view action showing full comment details → `app/Filament/Resources/CommentResource.php`
- [ ] [T030] [P1] [US2] Implement approve action → `app/Filament/Resources/CommentResource.php`
- [ ] [T031] [P1] [US2] Implement reject action → `app/Filament/Resources/CommentResource.php`
- [ ] [T032] [P1] [US2] Implement mark as spam action → `app/Filament/Resources/CommentResource.php`
- [ ] [T033] [P1] [US2] Implement delete action → `app/Filament/Resources/CommentResource.php`
- [ ] [T034] [P1] [US2] Implement bulk approve/reject/delete actions → `app/Filament/Resources/CommentResource.php`
- [ ] [T035] [P1] [US2] Add pending comments count badge in navigation → `app/Filament/Resources/CommentResource.php`
- [ ] [T036] [P1] [US2] Update comments_count on Post when approving/rejecting → `app/Models/Comment.php`
- [ ] [T037] [P1] [US2] Write tests for moderation workflow → `tests/Feature/CommentModerationTest.php`

---

## Phase 5: US5 – Protect from Spam Comments (P1) 🎯 MVP

- [ ] [T038] [P1] [US5] Add honeypot hidden field to comment form → `resources/views/livewire/comment-form.blade.php`
- [ ] [T039] [P1] [US5] Validate honeypot field is empty (reject if filled) → `app/Livewire/CommentForm.php`
- [ ] [T040] [P1] [US5] Silently reject honeypot-triggered submissions → `app/Livewire/CommentForm.php`
- [ ] [T041] [P1] [US5] Implement rate limiting (5 comments/minute per IP) → `app/Livewire/CommentForm.php`
- [ ] [T042] [P1] [US5] Display rate limit error message → `resources/views/livewire/comment-form.blade.php`
- [ ] [T043] [P1] [US5] Auto-hold comments containing links for moderation → `app/Livewire/CommentForm.php`
- [ ] [T044] [P1] [US5] Log blocked spam attempts → `app/Livewire/CommentForm.php`
- [ ] [T045] [P1] [US5] Write tests for spam protection → `tests/Feature/CommentSpamProtectionTest.php`

---

## Phase 6: US3 – Reply to Comments (P2)

- [ ] [T046] [P2] [US3] Create CommentReply Livewire component → `app/Livewire/CommentReply.php`
- [ ] [T047] [P2] [US3] Create comment-reply blade view with reply form → `resources/views/livewire/comment-reply.blade.php`
- [ ] [T048] [P2] [US3] Add "Reply" button to each approved comment → `resources/views/livewire/comment-list.blade.php`
- [ ] [T049] [P2] [US3] Store parent_id when submitting reply → `app/Livewire/CommentReply.php`
- [ ] [T050] [P2] [US3] Display nested replies with visual indentation → `resources/views/livewire/comment-list.blade.php`
- [ ] [T051] [P2] [US3] Enforce max depth (config setting, default 3) → `app/Livewire/CommentReply.php`
- [ ] [T052] [P2] [US3] Flatten replies deeper than max to parent level → `app/Livewire/CommentReply.php`
- [ ] [T053] [P2] [US3] Eager load replies with approvedReplies relationship → `app/Livewire/CommentList.php`
- [ ] [T054] [P2] [US3] Write tests for threaded replies → `tests/Feature/CommentThreadingTest.php`

---

## Phase 7: US4 – Receive Comment Notifications (P2)

- [ ] [T055] [P2] [US4] Create NewCommentNotification → `app/Notifications/NewCommentNotification.php`
- [ ] [T056] [P2] [US4] Create new-comment email view → `resources/views/emails/new-comment.blade.php`
- [ ] [T057] [P2] [US4] Create CommentReplyNotification → `app/Notifications/CommentReplyNotification.php`
- [ ] [T058] [P2] [US4] Create comment-reply email view → `resources/views/emails/comment-reply.blade.php`
- [ ] [T059] [P2] [US4] Create CommentObserver → `app/Observers/CommentObserver.php`
- [ ] [T060] [P2] [US4] Send NewCommentNotification to post author on approval → `app/Observers/CommentObserver.php`
- [ ] [T061] [P2] [US4] Send CommentReplyNotification to parent comment author (if subscribed) → `app/Observers/CommentObserver.php`
- [ ] [T062] [P2] [US4] Add notification preferences (is_notify_replies) checkbox to form → `resources/views/livewire/comment-form.blade.php`
- [ ] [T063] [P2] [US4] Include direct link to comment in notification emails → `resources/views/emails/*.blade.php`
- [ ] [T064] [P2] [US4] Write tests for notification delivery → `tests/Feature/CommentNotificationTest.php`

---

## Phase 8: US6 – Manage Comment Settings (P3)

- [ ] [T065] [P3] [US6] Add comments_enabled toggle to PostResource form → `app/Filament/Resources/PostResource.php`
- [ ] [T066] [P3] [US6] Implement global comments enable/disable in config → `config/comments.php`
- [ ] [T067] [P3] [US6] Implement auto-close after X days setting → `config/comments.php`
- [ ] [T068] [P3] [US6] Check auto-close in commentsAreEnabled() → `app/Models/Post.php`
- [ ] [T069] [P3] [US6] Add moderation mode toggle (require approval vs auto-approve) → `config/comments.php`
- [ ] [T070] [P3] [US6] Apply moderation mode setting in CommentForm → `app/Livewire/CommentForm.php`
- [ ] [T071] [P3] [US6] Write tests for comment settings → `tests/Feature/CommentSettingsTest.php`

---

## Phase 9: Frontend Integration

- [ ] [T072] [P1] Add CommentList and CommentForm to single post view → `resources/views/posts/show.blade.php`
- [ ] [T073] [P1] Display comment count on post cards → `resources/views/components/post-card.blade.php`
- [ ] [T074] [P2] Conditionally show comments section (only if enabled) → `resources/views/posts/show.blade.php`
- [ ] [T075] [P2] Show "Comments closed" message for disabled posts → `resources/views/posts/show.blade.php`
- [ ] [T076] [P2] Style comment components with TailwindCSS → `resources/views/livewire/*.blade.php`
- [ ] [T077] [P2] Add dark mode support to comment section → `resources/views/livewire/*.blade.php`
- [ ] [T078] [P2] Integrate Gravatar for commenter avatars → `resources/views/livewire/comment-list.blade.php`
- [ ] [T079] [P3] Add anchor links for direct comment linking (#comment-{id}) → `resources/views/livewire/comment-list.blade.php`

---

## Phase 10: Security & Polish

- [ ] [T080] [P1] Sanitize comment content (strip HTML tags) → `app/Livewire/CommentForm.php`
- [ ] [T081] [P1] Escape output in comment display → `resources/views/livewire/comment-list.blade.php`
- [ ] [T082] [P1] Create CommentPolicy with view, create, update, delete permissions → `app/Policies/CommentPolicy.php`
- [ ] [T083] [P1] Register CommentPolicy → `app/Providers/AppServiceProvider.php`
- [ ] [T084] [P2] Add admin reply action in CommentResource → `app/Filament/Resources/CommentResource.php`
- [ ] [T085] [P3] Implement comment editing within time window (15 min) → `app/Livewire/CommentList.php`
- [ ] [T086] [P1] Run full test suite and fix any failures
- [ ] [T087] [P1] Run `vendor/bin/pint --dirty` to fix code style
- [ ] [T088] [P1] Update README or documentation if needed
- [ ] [T089] [P1] Create PR and merge to main branch

---

## Summary

| Phase | Tasks | Priority Focus |
|-------|-------|----------------|
| Setup | 3 | P1 |
| Foundational | 10 | P1 |
| US1 – Submit Comment | 12 | P1 🎯 MVP |
| US2 – Moderate Comments | 12 | P1 🎯 MVP |
| US5 – Spam Protection | 8 | P1 🎯 MVP |
| US3 – Threaded Replies | 9 | P2 |
| US4 – Notifications | 10 | P2 |
| US6 – Settings | 7 | P3 |
| Frontend Integration | 8 | P1-P3 |
| Security & Polish | 10 | Mixed |

**Total Tasks: 89**
