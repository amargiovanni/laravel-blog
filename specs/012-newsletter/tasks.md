# Tasks – 012-newsletter

> Generated from spec.md, plan.md, and design artifacts.

---

## Phase 1: Setup

- [ ] [T001] [P1] Create feature branch `012-newsletter` from main (if not exists)
- [ ] [T002] [P1] Verify Laravel Mail and Queue are configured
- [ ] [T003] [P1] Verify email sending works (SMTP/provider configured)

---

## Phase 2: Foundational – Database & Models

- [ ] [T004] [P1] Create migration for `subscribers` table → `database/migrations/xxxx_create_subscribers_table.php`
- [ ] [T005] [P1] Create migration for `newsletters` table → `database/migrations/xxxx_create_newsletters_table.php`
- [ ] [T006] [P1] Create migration for `newsletter_sends` table (tracking) → `database/migrations/xxxx_create_newsletter_sends_table.php`
- [ ] [T007] [P1] Create Subscriber model with fillable, casts, scopes (verified, active) → `app/Models/Subscriber.php`
- [ ] [T008] [P1] Create Newsletter model with fillable, casts, relationships → `app/Models/Newsletter.php`
- [ ] [T009] [P1] Create NewsletterSend model for delivery tracking → `app/Models/NewsletterSend.php`
- [ ] [T010] [P1] Create SubscriberFactory → `database/factories/SubscriberFactory.php`
- [ ] [T011] [P1] Create NewsletterFactory → `database/factories/NewsletterFactory.php`
- [ ] [T012] [P1] Run migrations and verify schema

---

## Phase 3: US1 – Subscribe to Newsletter (P1) 🎯 MVP

- [ ] [T013] [P1] [US1] Create SubscriptionController → `app/Http/Controllers/SubscriptionController.php`
- [ ] [T014] [P1] [US1] Implement store() method for subscription → `app/Http/Controllers/SubscriptionController.php`
- [ ] [T015] [P1] [US1] Create SubscriptionConfirmation mailable → `app/Mail/SubscriptionConfirmation.php`
- [ ] [T016] [P1] [US1] Create subscription-confirmation email view → `resources/views/emails/subscription-confirmation.blade.php`
- [ ] [T017] [P1] [US1] Implement verify() method with signed URL validation → `app/Http/Controllers/SubscriptionController.php`
- [ ] [T018] [P1] [US1] Create newsletter-form Blade component → `resources/views/components/newsletter-form.blade.php`
- [ ] [T019] [P1] [US1] Register routes: POST /newsletter/subscribe, GET /newsletter/verify/{subscriber} → `routes/web.php`
- [ ] [T020] [P1] [US1] Handle duplicate subscription gracefully → `app/Http/Controllers/SubscriptionController.php`
- [ ] [T021] [P1] [US1] Add rate limiting (5 attempts/minute) to subscribe endpoint → `routes/web.php`
- [ ] [T022] [P1] [US1] Create verified confirmation view → `resources/views/newsletter/verified.blade.php`
- [ ] [T023] [P1] [US1] Write tests for subscription flow → `tests/Feature/NewsletterSubscriptionTest.php`

---

## Phase 4: US2 – Manage Subscriber List (P1) 🎯 MVP

- [ ] [T024] [P1] [US2] Create SubscriberResource for Filament → `app/Filament/Resources/SubscriberResource.php`
- [ ] [T025] [P1] [US2] Implement table with email, status, subscribed_at columns → `app/Filament/Resources/SubscriberResource.php`
- [ ] [T026] [P1] [US2] Add filters: verified, unsubscribed, date range → `app/Filament/Resources/SubscriberResource.php`
- [ ] [T027] [P1] [US2] Implement view/edit subscriber details → `app/Filament/Resources/SubscriberResource.php`
- [ ] [T028] [P1] [US2] Implement delete subscriber action → `app/Filament/Resources/SubscriberResource.php`
- [ ] [T029] [P1] [US2] Implement CSV export action → `app/Filament/Resources/SubscriberResource.php`
- [ ] [T030] [P1] [US2] Write tests for subscriber management → `tests/Feature/SubscriberManagementTest.php`

---

## Phase 5: US3 – Unsubscribe from Newsletter (P1) 🎯 MVP

- [ ] [T031] [P1] [US3] Create UnsubscribeController → `app/Http/Controllers/UnsubscribeController.php`
- [ ] [T032] [P1] [US3] Implement show() method for unsubscribe page → `app/Http/Controllers/UnsubscribeController.php`
- [ ] [T033] [P1] [US3] Implement unsubscribe() method to process unsubscription → `app/Http/Controllers/UnsubscribeController.php`
- [ ] [T034] [P1] [US3] Create unsubscribe view with confirmation → `resources/views/newsletter/unsubscribe.blade.php`
- [ ] [T035] [P1] [US3] Generate secure unsubscribe token on subscriber creation → `app/Models/Subscriber.php`
- [ ] [T036] [P1] [US3] Register routes: GET/POST /newsletter/unsubscribe/{token} → `routes/web.php`
- [ ] [T037] [P1] [US3] Write tests for unsubscribe flow → `tests/Feature/NewsletterUnsubscribeTest.php`

---

## Phase 6: US4 – Send Newsletter to Subscribers (P2)

- [ ] [T038] [P2] [US4] Create NewsletterResource for Filament → `app/Filament/Resources/NewsletterResource.php`
- [ ] [T039] [P2] [US4] Implement newsletter composer with subject and rich content editor → `app/Filament/Resources/NewsletterResource.php`
- [ ] [T040] [P2] [US4] Create SendNewsletterJob for queue processing → `app/Jobs/SendNewsletterJob.php`
- [ ] [T041] [P2] [US4] Create NewsletterMail mailable → `app/Mail/NewsletterMail.php`
- [ ] [T042] [P2] [US4] Create newsletter email view → `resources/views/emails/newsletter.blade.php`
- [ ] [T043] [P2] [US4] Implement batch processing (chunk subscribers) → `app/Jobs/SendNewsletterJob.php`
- [ ] [T044] [P2] [US4] Track delivery status in newsletter_sends table → `app/Jobs/SendNewsletterJob.php`
- [ ] [T045] [P2] [US4] Add unsubscribe link to newsletter emails → `resources/views/emails/newsletter.blade.php`
- [ ] [T046] [P2] [US4] Implement send action in NewsletterResource → `app/Filament/Resources/NewsletterResource.php`
- [ ] [T047] [P2] [US4] Display send status (sent/pending/failed counts) → `app/Filament/Resources/NewsletterResource.php`
- [ ] [T048] [P2] [US4] Implement scheduling (scheduled_at field) → `app/Filament/Resources/NewsletterResource.php`
- [ ] [T049] [P2] [US4] Create scheduled newsletter dispatcher command → `app/Console/Commands/SendScheduledNewsletters.php`
- [ ] [T050] [P2] [US4] Write tests for newsletter sending → `tests/Feature/NewsletterSendingTest.php`

---

## Phase 7: New Post Notifications

- [ ] [T051] [P2] Create SendNewPostNotificationJob → `app/Jobs/SendNewPostNotificationJob.php`
- [ ] [T052] [P2] Create NewPostNotification mailable → `app/Mail/NewPostNotification.php`
- [ ] [T053] [P2] Create new-post email view → `resources/views/emails/new-post.blade.php`
- [ ] [T054] [P2] Hook into post publish event (PostObserver) → `app/Observers/PostObserver.php`
- [ ] [T055] [P3] Add setting to enable/disable auto-notifications → `config/newsletter.php`
- [ ] [T056] [P2] Write tests for new post notifications → `tests/Feature/NewPostNotificationTest.php`

---

## Phase 8: US5 – Customize Email Templates (P3)

- [ ] [T057] [P3] [US5] Create base email layout → `resources/views/emails/layouts/newsletter.blade.php`
- [ ] [T058] [P3] [US5] Create consistent header component → `resources/views/emails/components/header.blade.php`
- [ ] [T059] [P3] [US5] Create consistent footer component with unsubscribe → `resources/views/emails/components/footer.blade.php`
- [ ] [T060] [P3] [US5] Implement email preview action in NewsletterResource → `app/Filament/Resources/NewsletterResource.php`
- [ ] [T061] [P3] [US5] Ensure email client compatibility (inline CSS) → `resources/views/emails/*.blade.php`

---

## Phase 9: Polish & Cross-Cutting Concerns

- [ ] [T062] [P2] Add List-Unsubscribe header to all newsletter emails → `app/Mail/NewsletterMail.php`
- [ ] [T063] [P2] Handle email delivery failures with retry logic → `app/Jobs/SendNewsletterJob.php`
- [ ] [T064] [P3] Log subscription/unsubscription events → `app/Http/Controllers/SubscriptionController.php`
- [ ] [T065] [P3] Add newsletter widget for sidebar → `app/Widgets/NewsletterWidget.php`
- [ ] [T066] [P1] Run full test suite and fix any failures
- [ ] [T067] [P1] Run `vendor/bin/pint --dirty` to fix code style
- [ ] [T068] [P1] Update README or documentation if needed
- [ ] [T069] [P1] Create PR and merge to main branch

---

## Summary

| Phase | Tasks | Priority Focus |
|-------|-------|----------------|
| Setup | 3 | P1 |
| Foundational | 9 | P1 |
| US1 – Subscribe | 11 | P1 🎯 MVP |
| US2 – Manage List | 7 | P1 🎯 MVP |
| US3 – Unsubscribe | 7 | P1 🎯 MVP |
| US4 – Send Newsletter | 13 | P2 |
| New Post Notifications | 6 | P2-P3 |
| US5 – Templates | 5 | P3 |
| Polish | 8 | Mixed |

**Total Tasks: 69**
