# Tasks – 013-contact-form

> Generated from spec.md, plan.md, and design artifacts.

---

## Phase 1: Setup

- [ ] [T001] [P1] Create feature branch `013-contact-form` from main (if not exists)
- [ ] [T002] [P1] Verify Laravel Mail is configured and functional
- [ ] [T003] [P1] Verify CSRF protection is enabled

---

## Phase 2: Foundational – Database & Model

- [ ] [T004] [P1] Create migration for `contact_messages` table with fields: id, name, email, subject, message, ip_address, user_agent, is_read, read_at, timestamps → `database/migrations/xxxx_create_contact_messages_table.php`
- [ ] [T005] [P1] Create ContactMessage model with fillable, casts, scopes (unread, read), methods (markAsRead, markAsUnread) → `app/Models/ContactMessage.php`
- [ ] [T006] [P1] Create ContactMessageFactory → `database/factories/ContactMessageFactory.php`
- [ ] [T007] [P1] Run migration and verify schema

---

## Phase 3: US1 – Submit Contact Message (P1) 🎯 MVP

- [ ] [T008] [P1] [US1] Create ContactFormRequest with validation rules → `app/Http/Requests/ContactFormRequest.php`
- [ ] [T009] [P1] [US1] Create ContactController with show() and store() methods → `app/Http/Controllers/ContactController.php`
- [ ] [T010] [P1] [US1] Register routes: GET /contact, POST /contact → `routes/web.php`
- [ ] [T011] [P1] [US1] Create contact page view with form → `resources/views/contact.blade.php`
- [ ] [T012] [P1] [US1] Create ContactForm Blade component with all fields → `app/View/Components/ContactForm.php`
- [ ] [T013] [P1] [US1] Create contact-form blade view → `resources/views/components/contact-form.blade.php`
- [ ] [T014] [P1] [US1] Implement name, email, subject, message fields with validation → `resources/views/components/contact-form.blade.php`
- [ ] [T015] [P1] [US1] Display inline validation errors → `resources/views/components/contact-form.blade.php`
- [ ] [T016] [P1] [US1] Display success flash message after submission → `resources/views/contact.blade.php`
- [ ] [T017] [P1] [US1] Create ContactFormSubmission mailable → `app/Mail/ContactFormSubmission.php`
- [ ] [T018] [P1] [US1] Create contact-submission email view → `resources/views/emails/contact-submission.blade.php`
- [ ] [T019] [P1] [US1] Send email notification on successful submission → `app/Http/Controllers/ContactController.php`
- [ ] [T020] [P1] [US1] Store IP address and user agent for tracking → `app/Http/Controllers/ContactController.php`
- [ ] [T021] [P1] [US1] Write tests for form submission → `tests/Feature/ContactFormTest.php`

---

## Phase 4: US2 – Protect Form from Spam (P1) 🎯 MVP

- [ ] [T022] [P1] [US2] Implement honeypot hidden field → `resources/views/components/contact-form.blade.php`
- [ ] [T023] [P1] [US2] Add honeypot validation (website field must be empty) → `app/Http/Requests/ContactFormRequest.php`
- [ ] [T024] [P1] [US2] Silently reject honeypot-filled submissions → `app/Http/Controllers/ContactController.php`
- [ ] [T025] [P1] [US2] Add rate limiting (5 attempts/minute per IP) → `routes/web.php`
- [ ] [T026] [P1] [US2] Display rate limit error message → `resources/views/contact.blade.php`
- [ ] [T027] [P1] [US2] Log blocked spam submissions → `app/Http/Controllers/ContactController.php`
- [ ] [T028] [P1] [US2] Write tests for honeypot rejection → `tests/Feature/ContactSpamProtectionTest.php`
- [ ] [T029] [P1] [US2] Write tests for rate limiting → `tests/Feature/ContactSpamProtectionTest.php`

---

## Phase 5: US3 – Manage Contact Messages (P2)

- [ ] [T030] [P2] [US3] Create ContactMessageResource for Filament → `app/Filament/Resources/ContactMessageResource.php`
- [ ] [T031] [P2] [US3] Implement table with name, email, subject, is_read, created_at columns → `app/Filament/Resources/ContactMessageResource.php`
- [ ] [T032] [P2] [US3] Add filters: read status, date range → `app/Filament/Resources/ContactMessageResource.php`
- [ ] [T033] [P2] [US3] Implement view action showing full message → `app/Filament/Resources/ContactMessageResource.php`
- [ ] [T034] [P2] [US3] Implement "Mark as Read" action → `app/Filament/Resources/ContactMessageResource.php`
- [ ] [T035] [P2] [US3] Implement "Mark as Unread" action → `app/Filament/Resources/ContactMessageResource.php`
- [ ] [T036] [P2] [US3] Implement delete action → `app/Filament/Resources/ContactMessageResource.php`
- [ ] [T037] [P2] [US3] Add unread count badge in navigation → `app/Filament/Resources/ContactMessageResource.php`
- [ ] [T038] [P2] [US3] Auto-mark as read when viewing message → `app/Filament/Resources/ContactMessageResource.php`
- [ ] [T039] [P2] [US3] Write tests for admin message management → `tests/Feature/ContactMessageManagementTest.php`

---

## Phase 6: US4 – Customize Form Fields (P3)

- [ ] [T040] [P3] [US4] Add configuration for custom fields → `config/contact.php`
- [ ] [T041] [P3] [US4] Implement dynamic field rendering from config → `app/View/Components/ContactForm.php`
- [ ] [T042] [P3] [US4] Store custom field data as JSON → `app/Models/ContactMessage.php`
- [ ] [T043] [P3] [US4] Display custom fields in admin view → `app/Filament/Resources/ContactMessageResource.php`

---

## Phase 7: Security

- [ ] [T044] [P1] Sanitize all input (strip HTML tags) → `app/Http/Controllers/ContactController.php`
- [ ] [T045] [P1] Escape output in admin views → `app/Filament/Resources/ContactMessageResource.php`
- [ ] [T046] [P1] Verify CSRF token is required → `app/Http/Controllers/ContactController.php`
- [ ] [T047] [P2] Add email Reply-To header with sender's email → `app/Mail/ContactFormSubmission.php`
- [ ] [T048] [P2] Write XSS prevention tests → `tests/Feature/ContactSecurityTest.php`

---

## Phase 8: Polish & Cross-Cutting Concerns

- [ ] [T049] [P2] Handle email delivery failures gracefully (log error, save message) → `app/Http/Controllers/ContactController.php`
- [ ] [T050] [P2] Style contact form with TailwindCSS → `resources/views/components/contact-form.blade.php`
- [ ] [T051] [P3] Add character counter for message field (5000 char limit) → `resources/views/components/contact-form.blade.php`
- [ ] [T052] [P3] Add dark mode support to contact form → `resources/views/components/contact-form.blade.php`
- [ ] [T053] [P1] Run full test suite and fix any failures
- [ ] [T054] [P1] Run `vendor/bin/pint --dirty` to fix code style
- [ ] [T055] [P1] Update README or documentation if needed
- [ ] [T056] [P1] Create PR and merge to main branch

---

## Summary

| Phase | Tasks | Priority Focus |
|-------|-------|----------------|
| Setup | 3 | P1 |
| Foundational | 4 | P1 |
| US1 – Submit Message | 14 | P1 🎯 MVP |
| US2 – Spam Protection | 8 | P1 🎯 MVP |
| US3 – Manage Messages | 10 | P2 |
| US4 – Custom Fields | 4 | P3 |
| Security | 5 | P1-P2 |
| Polish | 8 | Mixed |

**Total Tasks: 56**
