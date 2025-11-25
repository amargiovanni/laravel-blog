# Implementation Plan: Comments System

**Branch**: `015-comments-system` | **Date**: 2025-11-25 | **Spec**: [spec.md](spec.md)

## Summary

Implement a blog commenting system with guest comments, threaded replies, moderation workflow, spam protection, and email notifications. Uses Livewire for interactive comment forms and Filament for admin moderation.

## Technical Context

**Language/Version**: PHP 8.4+ with Laravel 12
**Primary Dependencies**: Filament 4.x, Livewire 3, Laravel Mail, Laravel Notifications
**Storage**: MySQL/SQLite via Eloquent ORM
**Testing**: Pest 4.x
**Performance Goals**: Comment submission < 2s, moderation actions < 1s

## Constitution Check

| Principle | Status | Notes |
|-----------|--------|-------|
| I. Test-First Development | ✅ PASS | Pest tests for all user stories |
| II. Laravel Conventions | ✅ PASS | Eloquent, Policies, Notifications |
| III. TALL Stack Compliance | ✅ PASS | Livewire for comment forms |
| IV. Code Quality Gates | ✅ PASS | Pint, Larastan, tests |
| V. Simplicity & YAGNI | ✅ PASS | Simple threading, no external services |

## Project Structure

```text
app/
├── Models/
│   └── Comment.php
├── Filament/Resources/
│   └── CommentResource.php
├── Livewire/
│   ├── CommentForm.php
│   ├── CommentList.php
│   └── CommentReply.php
├── Notifications/
│   ├── NewCommentNotification.php
│   └── CommentReplyNotification.php
├── Policies/
│   └── CommentPolicy.php
├── Observers/
│   └── CommentObserver.php
└── Http/Requests/
    └── CommentRequest.php

database/migrations/
├── xxxx_create_comments_table.php
└── xxxx_add_comments_enabled_to_posts_table.php

resources/views/
├── livewire/
│   ├── comment-form.blade.php
│   ├── comment-list.blade.php
│   └── comment-reply.blade.php
└── emails/
    ├── new-comment.blade.php
    └── comment-reply.blade.php

config/
└── comments.php
```

## Implementation Tasks

### Phase 1: Database & Models

| Task | Description | Acceptance |
|------|-------------|------------|
| T1.1 | Create comments migration | Table with all fields |
| T1.2 | Add comments_enabled to posts | Migration complete |
| T1.3 | Create Comment model | Model with relationships |
| T1.4 | Create CommentFactory | Factory for testing |
| T1.5 | Add comments relationship to Post | HasMany relationship |

### Phase 2: Comment Submission (US1)

| Task | Description | Acceptance |
|------|-------------|------------|
| T2.1 | Create CommentForm Livewire component | Component renders |
| T2.2 | Implement form validation | Errors displayed |
| T2.3 | Store comment with pending status | Comment saved to DB |
| T2.4 | Store IP and user agent | Metadata captured |
| T2.5 | Show success/awaiting moderation message | Feedback displayed |
| T2.6 | Create CommentList component | Comments displayed |

### Phase 3: Comment Moderation (US2)

| Task | Description | Acceptance |
|------|-------------|------------|
| T3.1 | Create CommentResource | Filament resource exists |
| T3.2 | Implement list with filters | Status filters work |
| T3.3 | Implement approve action | Status changes to approved |
| T3.4 | Implement reject action | Status changes to rejected |
| T3.5 | Implement delete action | Comment removed |
| T3.6 | Implement bulk actions | Multiple selection works |

### Phase 4: Threaded Replies (US3)

| Task | Description | Acceptance |
|------|-------------|------------|
| T4.1 | Add parent_id to comments | Self-referential FK |
| T4.2 | Create CommentReply component | Reply form renders |
| T4.3 | Implement nested display | Indentation visible |
| T4.4 | Enforce max depth | Deep replies flattened |
| T4.5 | Add replies relationship | Eager loading works |

### Phase 5: Notifications (US4)

| Task | Description | Acceptance |
|------|-------------|------------|
| T5.1 | Create NewCommentNotification | Notification class exists |
| T5.2 | Create CommentReplyNotification | Notification class exists |
| T5.3 | Create email templates | Templates render |
| T5.4 | Hook notifications to approval | Emails sent on approve |
| T5.5 | Add notification preferences | User can opt-out |

### Phase 6: Spam Protection (US5)

| Task | Description | Acceptance |
|------|-------------|------------|
| T6.1 | Implement honeypot field | Hidden field in form |
| T6.2 | Validate honeypot empty | Filled = rejected |
| T6.3 | Implement rate limiting | 5/min per IP |
| T6.4 | Auto-hold comments with links | Sent to moderation |
| T6.5 | Log spam attempts | Logged for analysis |

### Phase 7: Settings (US6)

| Task | Description | Acceptance |
|------|-------------|------------|
| T7.1 | Create config/comments.php | Config file exists |
| T7.2 | Add per-post toggle | Post form has checkbox |
| T7.3 | Implement auto-close | Old posts closed |
| T7.4 | Add moderation mode setting | Global toggle works |

### Phase 8: Frontend Integration

| Task | Description | Acceptance |
|------|-------------|------------|
| T8.1 | Add comments to post view | Components in template |
| T8.2 | Display comment count | Count shown on cards |
| T8.3 | Style with TailwindCSS | Matches site design |
| T8.4 | Add dark mode support | Theme compatible |
| T8.5 | Add Gravatar integration | Avatars displayed |

### Phase 9: Testing

| Task | Description | Acceptance |
|------|-------------|------------|
| T9.1 | Test comment submission | E2E works |
| T9.2 | Test moderation workflow | Approve/reject works |
| T9.3 | Test threading | Replies nest correctly |
| T9.4 | Test notifications | Emails sent |
| T9.5 | Test spam protection | Bots blocked |
| T9.6 | Test rate limiting | Limits enforced |
| T9.7 | Test XSS prevention | Input sanitized |

## Dependencies

### External Packages

| Package | Version | Purpose |
|---------|---------|---------|
| filament/filament | ^4.0 | Admin moderation |
| livewire/livewire | ^3.0 | Interactive forms |

Uses Laravel built-in: Mail, Notifications, Rate Limiting.

### Internal Dependencies

- Post model from 001-blog-engine (add relationship)
- User model (for author notifications)
- Public frontend from 007-public-frontend (integration point)

### Feature Dependencies

| Feature | Dependency Type | Description |
|---------|----------------|-------------|
| 007-public-frontend | Required | Comments integrate into post view |
| 001-blog-engine | Required | Post model and relationships |

## Risk Assessment

| Risk | Probability | Impact | Mitigation |
|------|-------------|--------|------------|
| Spam volume | High | Medium | Honeypot + rate limiting |
| Performance with many comments | Medium | Medium | Pagination, eager loading |
| XSS in comments | Low | High | Strict sanitization |
| Email delivery issues | Low | Low | Queue + retry logic |

## Artifacts

- [data-model.md](data-model.md) - Comment schema and relationships
- [contracts/routes.md](contracts/routes.md) - Route specifications (none - Livewire)
