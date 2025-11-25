# Feature Specification: Post Revisions

**Feature Branch**: `006-post-revisions`
**Created**: 2025-11-25
**Status**: Draft
**Input**: User description: "Post Revisions - Cronologia delle modifiche ai post con possibilità di visualizzare differenze tra versioni e ripristinare versioni precedenti."

## User Scenarios & Testing *(mandatory)*

### User Story 1 - View Post Revision History (Priority: P1)

An author needs to see the complete history of changes made to a post, including who made each change and when.

**Why this priority**: Understanding change history is the foundation for any revision management system.

**Independent Test**: Can be tested by making multiple edits to a post and verifying each revision is recorded and visible.

**Acceptance Scenarios**:

1. **Given** a post with multiple edits, **When** the author opens the revision panel, **Then** they see a chronological list of revisions
2. **Given** the revision list, **When** viewing an entry, **Then** it shows timestamp, author name, and revision number
3. **Given** a post with 20 revisions, **When** viewing the list, **Then** all revisions are accessible (with pagination if needed)
4. **Given** a new post that has never been edited, **When** viewing revisions, **Then** only the initial version is shown

---

### User Story 2 - Compare Revision Differences (Priority: P1)

An editor needs to compare two versions of a post to understand what changed between them.

**Why this priority**: Comparing changes is essential for editorial review and understanding modification history.

**Independent Test**: Can be tested by selecting two revisions and verifying differences are highlighted correctly.

**Acceptance Scenarios**:

1. **Given** revision history, **When** the user selects two revisions, **Then** a side-by-side comparison is displayed
2. **Given** the comparison view, **When** viewing content differences, **Then** additions are highlighted in green
3. **Given** the comparison view, **When** viewing content differences, **Then** deletions are highlighted in red
4. **Given** the comparison view, **When** title or other fields changed, **Then** those changes are also displayed

---

### User Story 3 - Restore Previous Revision (Priority: P1)

An author needs to restore a post to a previous version when recent changes need to be reverted.

**Why this priority**: Restoration capability is the primary value of maintaining revision history.

**Independent Test**: Can be tested by restoring a previous revision and verifying the post content matches that version.

**Acceptance Scenarios**:

1. **Given** a revision in the history, **When** the author clicks "Restore this revision", **Then** a confirmation dialog appears
2. **Given** confirmation accepted, **When** restoration completes, **Then** the post content matches the selected revision
3. **Given** a restored post, **When** viewing revision history, **Then** a new revision is created noting the restoration
4. **Given** a restored post, **When** viewing it, **Then** the current content shows the restored version

---

### User Story 4 - Auto-Save Drafts as Revisions (Priority: P2)

The system needs to automatically save work-in-progress to prevent data loss.

**Why this priority**: Auto-save protects against accidental data loss but manual saves are the primary workflow.

**Independent Test**: Can be tested by editing a post, waiting for auto-save, and verifying the changes are preserved.

**Acceptance Scenarios**:

1. **Given** a post being edited, **When** 60 seconds pass without manual save, **Then** an auto-save revision is created
2. **Given** auto-save triggers, **When** viewing revision history, **Then** auto-saves are marked distinctly from manual saves
3. **Given** browser closes unexpectedly, **When** editor reopens the post, **Then** auto-saved content is offered for recovery
4. **Given** multiple auto-saves, **When** manual save occurs, **Then** auto-save revisions are consolidated

---

### User Story 5 - Manage Revision Storage (Priority: P3)

An administrator needs to control how many revisions are stored to manage storage space.

**Why this priority**: Storage management is important for long-term maintenance but not for basic functionality.

**Independent Test**: Can be tested by configuring revision limits and verifying old revisions are pruned correctly.

**Acceptance Scenarios**:

1. **Given** settings panel, **When** admin views revision settings, **Then** they see option to limit revisions per post
2. **Given** limit set to 25, **When** a post exceeds 25 revisions, **Then** the oldest revisions are automatically deleted
3. **Given** revision limit setting, **When** changed, **Then** existing posts are pruned on next edit
4. **Given** a specific revision, **When** admin marks it as "protected", **Then** it is exempt from automatic deletion

---

### Edge Cases

- What happens when restoring a revision that references deleted media? (Restore text, warn about missing media)
- What happens when two users edit simultaneously? (Last save wins, notification to other user)
- What happens when a very large post has many revisions? (Implement diff-based storage)
- What happens when revision storage limit is reached? (Delete oldest non-protected revisions)
- How does the system handle revision of post metadata (categories, tags)? (Store as part of revision)

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: System MUST create a revision each time a post is saved
- **FR-002**: System MUST store complete post content in each revision (title, content, excerpt)
- **FR-003**: System MUST record author and timestamp for each revision
- **FR-004**: System MUST display revision history in chronological order
- **FR-005**: System MUST provide side-by-side comparison of any two revisions
- **FR-006**: System MUST highlight additions and deletions in comparison view
- **FR-007**: System MUST allow restoring post to any previous revision
- **FR-008**: System MUST create a new revision when restoring (preserving current state)
- **FR-009**: System MUST auto-save drafts at configurable intervals (default: 60 seconds)
- **FR-010**: System MUST distinguish auto-save revisions from manual saves in history
- **FR-011**: System MUST allow configuring maximum revisions per post
- **FR-012**: System MUST automatically prune old revisions when limit is exceeded
- **FR-013**: System MUST allow protecting specific revisions from automatic deletion
- **FR-014**: System MUST track revision of post metadata (categories, tags, featured image)
- **FR-015**: System MUST restrict revision access to users with edit permissions for the post

### Key Entities

- **Revision**: Snapshot of post state including content, metadata, author, and timestamp
- **Revision Settings**: Global configuration for auto-save interval and retention limits

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: All post saves create revisions with 100% reliability
- **SC-002**: Revision comparison loads in under 2 seconds for posts of any size
- **SC-003**: Post restoration completes in under 3 seconds
- **SC-004**: Auto-save triggers without interrupting user typing experience
- **SC-005**: Storage usage per revision is optimized (under 1.5x the post content size)
- **SC-006**: 100% of restoration operations result in accurate content recovery

## Assumptions

- Posts and pages share the same revision system
- The existing post editing interface can accommodate a revision panel
- Revision storage uses efficient diff-based compression for large content
- Auto-save feature works with the existing content editor
