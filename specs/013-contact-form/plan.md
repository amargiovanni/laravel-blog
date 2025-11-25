# Implementation Plan: Contact Form

**Branch**: `013-contact-form` | **Date**: 2025-11-25 | **Spec**: [spec.md](spec.md)

## Summary

Implement contact form with validation, honeypot spam protection, rate limiting, email notifications, and admin message management via Filament.

## Technical Context

**Language/Version**: PHP 8.4+ with Laravel 12
**Primary Dependencies**: Filament 4.x, Laravel Mail
**Storage**: MySQL/SQLite for messages
**Testing**: Pest 4.x

## Constitution Check

All principles: ✅ PASS

## Project Structure

```text
app/
├── Models/
│   └── ContactMessage.php
├── Filament/Resources/
│   └── ContactMessageResource.php
├── Mail/
│   └── ContactFormSubmission.php
├── Http/
│   ├── Controllers/
│   │   └── ContactController.php
│   └── Requests/
│       └── ContactFormRequest.php
└── View/Components/
    └── ContactForm.php

database/migrations/
└── create_contact_messages_table.php

resources/views/
├── contact.blade.php
├── emails/
│   └── contact-submission.blade.php
└── components/
    └── contact-form.blade.php
```

## Implementation Tasks

### Phase 1: Database & Model

| Task | Description | Acceptance |
|------|-------------|------------|
| T1.1 | Create contact_messages migration | Table exists with all fields |
| T1.2 | Create ContactMessage model | Model with fillable and casts |
| T1.3 | Add read status tracking | is_read boolean works |

### Phase 2: Form & Controller

| Task | Description | Acceptance |
|------|-------------|------------|
| T2.1 | Create ContactFormRequest | Validation rules defined |
| T2.2 | Create ContactController | Controller handles submission |
| T2.3 | Add contact route | Route accessible |
| T2.4 | Create contact page view | Form displays correctly |

### Phase 3: Form Component

| Task | Description | Acceptance |
|------|-------------|------------|
| T3.1 | Create ContactForm component | Component renders |
| T3.2 | Implement name field | Field validates |
| T3.3 | Implement email field | Email format validated |
| T3.4 | Implement subject field | Field works |
| T3.5 | Implement message field | Character limit enforced |
| T3.6 | Display validation errors | Errors shown inline |
| T3.7 | Show success message | Flash message works |

### Phase 4: Spam Protection

| Task | Description | Acceptance |
|------|-------------|------------|
| T4.1 | Implement honeypot field | Hidden field present |
| T4.2 | Validate honeypot on submit | Bot submissions rejected |
| T4.3 | Add rate limiting | IP throttled after limit |
| T4.4 | Log blocked submissions | Logging for debugging |

### Phase 5: Email Notification

| Task | Description | Acceptance |
|------|-------------|------------|
| T5.1 | Create ContactFormSubmission mailable | Mailable exists |
| T5.2 | Create email template | Professional template |
| T5.3 | Send on form submission | Email sent to admin |
| T5.4 | Handle email failures | Error logged, message saved |

### Phase 6: Admin Interface

| Task | Description | Acceptance |
|------|-------------|------------|
| T6.1 | Create ContactMessageResource | Filament resource exists |
| T6.2 | Implement list view | Messages listed |
| T6.3 | Implement view action | Full message viewable |
| T6.4 | Implement mark as read | Status toggles |
| T6.5 | Implement delete action | Messages deletable |
| T6.6 | Add unread count badge | Badge shows in nav |

### Phase 7: Security

| Task | Description | Acceptance |
|------|-------------|------------|
| T7.1 | Sanitize all input | XSS prevented |
| T7.2 | Add CSRF protection | Token validated |
| T7.3 | Escape output in admin | Safe display |

### Phase 8: Testing

| Task | Description | Acceptance |
|------|-------------|------------|
| T8.1 | Test form submission | E2E works |
| T8.2 | Test validation errors | Errors displayed |
| T8.3 | Test honeypot rejection | Bots blocked |
| T8.4 | Test rate limiting | Throttle works |
| T8.5 | Test email sending | Email received |
| T8.6 | Test admin functions | CRUD works |
| T8.7 | Test XSS prevention | No vulnerabilities |

## Dependencies

### External Packages

| Package | Version | Purpose |
|---------|---------|---------|
| filament/filament | ^4.0 | Admin panel |

Uses Laravel built-in: Mail, Validation, Rate Limiting.

### Internal Dependencies

- Settings for admin email address
- Public frontend layout from 007-public-frontend

## Risk Assessment

| Risk | Probability | Impact | Mitigation |
|------|-------------|--------|------------|
| Spam submissions | Medium | Medium | Honeypot + rate limiting |
| Email delivery issues | Low | Medium | Log messages, save to DB |
| XSS attacks | Low | High | Input sanitization |

## Artifacts

- [research.md](research.md) - Spam protection and security research
- [data-model.md](data-model.md) - Contact message schema
- [quickstart.md](quickstart.md) - Quick implementation guide
- [contracts/routes.md](contracts/routes.md) - Route specifications
