# Feature Specification: RSS Feed

**Feature Branch**: `010-rss-feed`
**Created**: 2025-11-25
**Status**: Draft
**Input**: User description: "RSS Feed - Feed RSS per i lettori che vogliono seguire il blog tramite feed reader, con supporto per feed principale, per categoria e per autore."

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Subscribe to Main Blog Feed (Priority: P1)

A reader wants to subscribe to the blog's RSS feed to receive updates in their feed reader.

**Why this priority**: The main RSS feed is the fundamental feature that enables subscription-based reading.

**Independent Test**: Can be tested by accessing /feed and importing it into a standard RSS reader.

**Acceptance Scenarios**:

1. **Given** published posts exist, **When** accessing /feed, **Then** valid RSS XML is returned
2. **Given** the RSS feed, **When** imported into a feed reader, **Then** posts display correctly with title, content, and date
3. **Given** a new post is published, **When** the feed is refreshed, **Then** the new post appears at the top
4. **Given** the feed, **When** viewing an entry, **Then** it includes author name and categories

---

### User Story 2 - Subscribe to Category Feed (Priority: P2)

A reader wants to follow only posts from specific categories they're interested in.

**Why this priority**: Category feeds enable targeted subscriptions but main feed covers most use cases.

**Independent Test**: Can be tested by accessing /category/{slug}/feed and verifying only category posts appear.

**Acceptance Scenarios**:

1. **Given** posts in "Technology" category, **When** accessing /category/technology/feed, **Then** only those posts are included
2. **Given** a category feed, **When** validating it, **Then** it has the category name in the feed title
3. **Given** a post added to a category, **When** feed refreshes, **Then** the new post appears

---

### User Story 3 - Subscribe to Author Feed (Priority: P3)

A reader wants to follow a specific author's posts.

**Why this priority**: Author feeds are a niche feature for author-focused blogs.

**Independent Test**: Can be tested by accessing /author/{username}/feed and verifying only that author's posts appear.

**Acceptance Scenarios**:

1. **Given** posts by "John Doe", **When** accessing /author/john-doe/feed, **Then** only John's posts are included
2. **Given** an author feed, **When** validating it, **Then** it has the author name in the feed title

---

### User Story 4 - Discover Feed URLs (Priority: P2)

A reader needs to easily find and subscribe to available RSS feeds.

**Why this priority**: Feed discoverability improves subscription rates but technical users can find feeds manually.

**Independent Test**: Can be tested by checking page source for RSS link tags and visible feed icons.

**Acceptance Scenarios**:

1. **Given** any blog page, **When** viewing page source, **Then** RSS auto-discovery link tag is present
2. **Given** the blog header/footer, **When** viewing it, **Then** an RSS icon/link is visible
3. **Given** a category page, **When** viewing it, **Then** a link to the category-specific feed is available

---

### Edge Cases

- What happens when there are no published posts? (Return valid empty feed)
- What happens with posts containing special characters? (Properly escape for XML)
- What happens with very long post content? (Provide excerpt or truncate)
- What happens when feed is accessed with invalid category slug? (Return 404)
- How many posts should be included in the feed? (Last 20 posts, configurable)

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: System MUST generate valid RSS 2.0 feed at /feed
- **FR-002**: System MUST include post title, content/excerpt, author, date, and permalink
- **FR-003**: System MUST include post categories and tags as feed item categories
- **FR-004**: System MUST order feed entries by publication date (newest first)
- **FR-005**: System MUST limit feed to most recent posts (default: 20)
- **FR-006**: System MUST generate category-specific feeds at /category/{slug}/feed
- **FR-007**: System MUST generate author-specific feeds at /author/{username}/feed
- **FR-008**: System MUST include RSS auto-discovery link in HTML head
- **FR-009**: System MUST properly escape special characters for XML
- **FR-010**: System MUST exclude draft, scheduled, and private posts
- **FR-011**: System MUST include blog title, description, and link in feed metadata
- **FR-012**: System MUST include featured images as media enclosures when available
- **FR-013**: System MUST set appropriate Content-Type header (application/rss+xml)
- **FR-014**: System SHOULD support comments feed for individual posts

### Key Entities

- **RSS Feed**: XML document containing channel metadata and recent post entries
- **Feed Entry**: Individual post representation with required RSS elements

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: RSS feed validates against RSS 2.0 specification
- **SC-002**: Feed generates in under 500 milliseconds
- **SC-003**: 100% of published posts appear in feed within 5 minutes of publication
- **SC-004**: Feed is correctly parsed by major feed readers (Feedly, Inoreader, NetNewsWire)
- **SC-005**: RSS auto-discovery works in all major browsers
- **SC-006**: Feed content displays correctly without rendering issues in readers

## Assumptions

- The blog has a title and description configured in settings
- Post content is suitable for RSS (HTML content, not requiring JavaScript)
- Featured images are accessible via public URLs
- Category and author slugs follow URL-safe patterns
