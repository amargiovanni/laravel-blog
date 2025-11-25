# Feature Specification: Comments System

**Feature Branch**: `015-comments-system`
**Created**: 2025-11-25
**Status**: Draft
**Input**: User description: "Comments System - Sistema di commenti per i post del blog con moderazione, notifiche, threading e protezione antispam."

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Submit a Comment (Priority: P1)

A visitor wants to leave a comment on a blog post to engage with the content or ask questions.

**Why this priority**: Comment submission is the core functionality that enables reader engagement.

**Independent Test**: Can be tested by filling the comment form and verifying the comment appears after submission/approval.

**Acceptance Scenarios**:

1. **Given** a post with comments enabled, **When** a visitor views it, **Then** a comment form is displayed below the content
2. **Given** the comment form, **When** filling name, email, and message and submitting, **Then** a success message is shown
3. **Given** moderation is enabled, **When** a comment is submitted, **Then** it shows "awaiting moderation" status
4. **Given** moderation is disabled, **When** a comment is submitted, **Then** it appears immediately on the page
5. **Given** invalid form data, **When** submitting, **Then** clear validation errors are displayed

---

### User Story 2 - Moderate Comments (Priority: P1)

An administrator needs to review, approve, reject, or delete comments to maintain content quality.

**Why this priority**: Moderation is essential for preventing spam and inappropriate content.

**Independent Test**: Can be tested by submitting a comment and then approving/rejecting it in admin panel.

**Acceptance Scenarios**:

1. **Given** the admin panel, **When** navigating to Comments, **Then** a list of all comments with status is displayed
2. **Given** a pending comment, **When** admin clicks "Approve", **Then** the comment becomes visible on the post
3. **Given** a pending comment, **When** admin clicks "Reject", **Then** the comment is marked as rejected and hidden
4. **Given** any comment, **When** admin clicks "Delete", **Then** the comment is permanently removed
5. **Given** the comment list, **When** filtering by status (pending/approved/rejected), **Then** only matching comments are shown

---

### User Story 3 - Reply to Comments (Priority: P2)

A visitor or the post author wants to reply to an existing comment, creating a threaded conversation.

**Why this priority**: Threading improves discussion quality but flat comments are functional.

**Independent Test**: Can be tested by clicking "Reply" on a comment and verifying the reply appears nested.

**Acceptance Scenarios**:

1. **Given** an approved comment, **When** clicking "Reply", **Then** a reply form appears below that comment
2. **Given** a reply submitted, **When** approved, **Then** it appears nested under the parent comment
3. **Given** nested comments, **When** viewing the thread, **Then** visual indentation shows hierarchy
4. **Given** deep nesting (3+ levels), **When** viewing, **Then** a maximum depth is enforced for readability

---

### User Story 4 - Receive Comment Notifications (Priority: P2)

A post author wants to be notified when someone comments on their post, and commenters want to know when they receive replies.

**Why this priority**: Notifications increase engagement but comments work without them.

**Independent Test**: Can be tested by submitting a comment and verifying notification email is sent.

**Acceptance Scenarios**:

1. **Given** a new comment on a post, **When** approved, **Then** the post author receives an email notification
2. **Given** a reply to a comment, **When** approved, **Then** the original commenter receives an email (if subscribed)
3. **Given** comment notification settings, **When** author disables them, **Then** no notifications are sent
4. **Given** an email notification, **When** clicking the link, **Then** user is taken directly to the comment

---

### User Story 5 - Protect from Spam Comments (Priority: P1)

The system needs to prevent automated spam submissions without frustrating legitimate users.

**Why this priority**: Without spam protection, comments become unusable due to spam volume.

**Independent Test**: Can be tested by simulating bot submissions and verifying they are blocked.

**Acceptance Scenarios**:

1. **Given** the comment form, **When** a bot fills the honeypot field, **Then** submission is silently rejected
2. **Given** too many submissions from same IP, **When** attempted, **Then** rate limiting blocks further submissions
3. **Given** a suspicious comment, **When** detected by spam filter, **Then** it is auto-held for moderation
4. **Given** a legitimate user, **When** submitting normally, **Then** spam protection doesn't interfere

---

### User Story 6 - Manage Comment Settings (Priority: P3)

An administrator needs to configure comment behavior globally and per-post.

**Why this priority**: Configuration enhances flexibility but default settings work for most cases.

**Independent Test**: Can be tested by changing settings and verifying behavior changes accordingly.

**Acceptance Scenarios**:

1. **Given** global settings, **When** admin disables comments site-wide, **Then** no posts show comment forms
2. **Given** a specific post, **When** admin disables comments for it, **Then** only that post hides the form
3. **Given** auto-close setting (e.g., 30 days), **When** post is older, **Then** comments are automatically disabled
4. **Given** moderation settings, **When** changed, **Then** new comments follow the new rules

---

### Edge Cases

- What happens when a parent comment is deleted? (Delete replies or orphan them with "deleted" placeholder)
- What happens with very long comments? (Enforce character limit, e.g., 2000 characters)
- What happens when commenter email matches registered user? (Link to user profile if public)
- What happens with comments containing links? (Auto-hold for moderation or filter)
- How does system handle HTML in comments? (Strip tags or allow limited markdown)
- What happens when comment author wants to edit? (Allow within time window, e.g., 15 minutes)

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: System MUST display comment form on posts with comments enabled
- **FR-002**: System MUST require name, email, and message fields for guest comments
- **FR-003**: System MUST validate email format before submission
- **FR-004**: System MUST store commenter IP address and user agent for spam analysis
- **FR-005**: System MUST support comment moderation (pending, approved, rejected, spam)
- **FR-006**: System MUST allow admin to approve, reject, or delete comments
- **FR-007**: System MUST display approved comments on post pages
- **FR-008**: System MUST support threaded/nested replies up to configurable depth (default: 3)
- **FR-009**: System MUST implement spam protection (honeypot field)
- **FR-010**: System MUST implement rate limiting per IP (5 comments/minute)
- **FR-011**: System MUST send notification to post author on new comment (configurable)
- **FR-012**: System MUST send notification to parent comment author on reply (if subscribed)
- **FR-013**: System MUST allow enabling/disabling comments per post
- **FR-014**: System MUST support global comment settings (enable/disable, moderation mode)
- **FR-015**: System MUST sanitize comment content to prevent XSS
- **FR-016**: System MUST display comment count on post cards and single post
- **FR-017**: System SHOULD support auto-closing comments after configurable days
- **FR-018**: System SHOULD allow logged-in users to comment without entering name/email
- **FR-019**: System SHOULD support comment editing within time window

### Key Entities

- **Comment**: Content, author info, status, parent_id (for threading), timestamps
- **Comment Settings**: Global and per-post configuration for comment behavior

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: Comment submission completes in under 2 seconds
- **SC-002**: Spam protection blocks 95%+ of automated submissions
- **SC-003**: Comment moderation actions complete in under 1 second
- **SC-004**: Threaded comments render correctly up to 3 levels deep
- **SC-005**: Zero XSS vulnerabilities in comment display
- **SC-006**: Notification emails sent within 1 minute of comment approval
- **SC-007**: Comment list in admin loads in under 2 seconds for 10,000+ comments

## Assumptions

- Posts have a `comments_enabled` field or use global default
- Email sending is configured for notifications
- Admin users have permission to moderate comments
- Guest comments don't require account creation
- Gravatar or similar service available for commenter avatars
