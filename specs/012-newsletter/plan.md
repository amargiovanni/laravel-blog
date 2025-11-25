# Implementation Plan: Newsletter

**Branch**: `012-newsletter` | **Date**: 2025-11-25 | **Spec**: [spec.md](spec.md)

## Summary

Implement newsletter subscription system with double opt-in, subscriber management via Filament admin, newsletter composer, and automatic new post notifications. Uses Laravel queues for bulk email delivery.

## Technical Context

**Language/Version**: PHP 8.4+ with Laravel 12
**Primary Dependencies**: Filament 4.x, Laravel Mail, Laravel Queue
**Storage**: MySQL/SQLite for subscribers and newsletters
**Testing**: Pest 4.x

## Constitution Check

All principles: ✅ PASS

## Project Structure

```text
app/
├── Models/
│   ├── Subscriber.php
│   └── Newsletter.php
├── Filament/Resources/
│   ├── SubscriberResource.php
│   └── NewsletterResource.php
├── Mail/
│   ├── SubscriptionConfirmation.php
│   ├── NewsletterMail.php
│   └── NewPostNotification.php
├── Jobs/
│   ├── SendNewsletterJob.php
│   └── SendNewPostNotificationJob.php
├── Notifications/
│   └── SubscriptionConfirmed.php
└── Http/Controllers/
    ├── SubscriptionController.php
    └── UnsubscribeController.php

database/migrations/
├── create_subscribers_table.php
└── create_newsletters_table.php

resources/views/
├── emails/
│   ├── subscription-confirmation.blade.php
│   ├── newsletter.blade.php
│   └── new-post.blade.php
└── components/
    └── newsletter-form.blade.php
```

## Implementation Tasks

### Phase 1: Database & Models

| Task | Description | Acceptance |
|------|-------------|------------|
| T1.1 | Create subscribers migration | Table created with all fields |
| T1.2 | Create newsletters migration | Table created with all fields |
| T1.3 | Create Subscriber model | Model with relationships |
| T1.4 | Create Newsletter model | Model with relationships |

### Phase 2: Subscription Flow

| Task | Description | Acceptance |
|------|-------------|------------|
| T2.1 | Create SubscriptionController | Controller handles subscribe |
| T2.2 | Create subscription form component | Form renders on frontend |
| T2.3 | Implement double opt-in email | Confirmation email sent |
| T2.4 | Implement verification endpoint | Email verified on click |
| T2.5 | Handle duplicate subscriptions | Proper message shown |

### Phase 3: Unsubscribe Flow

| Task | Description | Acceptance |
|------|-------------|------------|
| T3.1 | Create UnsubscribeController | Controller handles unsubscribe |
| T3.2 | Create unsubscribe page | Page with confirmation |
| T3.3 | Generate secure unsubscribe tokens | Token-based unsubscribe |
| T3.4 | Add unsubscribe link to emails | Link in all newsletter emails |

### Phase 4: Admin - Subscriber Management

| Task | Description | Acceptance |
|------|-------------|------------|
| T4.1 | Create SubscriberResource | Filament resource exists |
| T4.2 | Implement list view | Subscribers listed with filters |
| T4.3 | Implement subscriber details | View single subscriber |
| T4.4 | Implement delete functionality | Subscribers can be removed |
| T4.5 | Implement CSV export | Export action works |

### Phase 5: Admin - Newsletter Composer

| Task | Description | Acceptance |
|------|-------------|------------|
| T5.1 | Create NewsletterResource | Filament resource exists |
| T5.2 | Implement newsletter composer | Rich text editor for content |
| T5.3 | Implement preview functionality | Email preview shown |
| T5.4 | Implement send action | Newsletter queued for sending |
| T5.5 | Implement scheduling | Future send date works |
| T5.6 | Track send status | Status updates visible |

### Phase 6: Email Delivery

| Task | Description | Acceptance |
|------|-------------|------------|
| T6.1 | Create SendNewsletterJob | Job processes newsletter |
| T6.2 | Create NewsletterMail mailable | Email sent correctly |
| T6.3 | Implement batch processing | Emails sent in chunks |
| T6.4 | Track delivery status | Sent/failed tracked |
| T6.5 | Handle failures gracefully | Retries and logging |

### Phase 7: New Post Notifications

| Task | Description | Acceptance |
|------|-------------|------------|
| T7.1 | Create SendNewPostNotificationJob | Job exists |
| T7.2 | Create NewPostNotification mail | Email template works |
| T7.3 | Hook into post publish event | Auto-trigger on publish |
| T7.4 | Add setting to enable/disable | Toggle in settings |

### Phase 8: Email Templates

| Task | Description | Acceptance |
|------|-------------|------------|
| T8.1 | Create base email layout | Consistent styling |
| T8.2 | Create confirmation template | Professional design |
| T8.3 | Create newsletter template | Content displays well |
| T8.4 | Create new post template | Post preview in email |
| T8.5 | Ensure email client compatibility | Works in major clients |

### Phase 9: Testing

| Task | Description | Acceptance |
|------|-------------|------------|
| T9.1 | Test subscription flow | E2E subscription works |
| T9.2 | Test double opt-in | Verification works |
| T9.3 | Test unsubscribe flow | Unsubscribe works |
| T9.4 | Test newsletter sending | Emails delivered |
| T9.5 | Test admin functions | CRUD works |
| T9.6 | Test rate limiting | No spam possible |

## Dependencies

### External Packages

| Package | Version | Purpose |
|---------|---------|---------|
| filament/filament | ^4.0 | Admin panel |

Uses Laravel built-in: Mail, Queue, Notifications.

### Internal Dependencies

- User model for admin permissions
- Post model for new post notifications
- Settings from 002-backoffice-enhancements

## Risk Assessment

| Risk | Probability | Impact | Mitigation |
|------|-------------|--------|------------|
| Email deliverability | Medium | High | Proper DNS, throttling |
| Spam complaints | Low | High | Double opt-in, easy unsubscribe |
| Queue failures | Low | Medium | Retry logic, monitoring |

## Artifacts

- [research.md](research.md) - Email delivery and compliance research
- [data-model.md](data-model.md) - Subscriber and newsletter schema
- [quickstart.md](quickstart.md) - Quick implementation guide
- [contracts/routes.md](contracts/routes.md) - Route specifications
