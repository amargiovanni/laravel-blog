# Feature Specification: XML Sitemap

**Feature Branch**: `009-xml-sitemap`
**Created**: 2025-11-25
**Status**: Draft
**Input**: User description: "XML Sitemap - Generazione automatica di sitemap XML per SEO con supporto per post, pagine, categorie, tag e immagini."

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Generate XML Sitemap Automatically (Priority: P1)

Search engines need an XML sitemap to efficiently crawl and index all published content on the blog.

**Why this priority**: Sitemaps are fundamental for SEO and search engine visibility.

**Independent Test**: Can be tested by accessing /sitemap.xml and verifying valid XML structure with all published URLs.

**Acceptance Scenarios**:

1. **Given** published posts exist, **When** accessing /sitemap.xml, **Then** all post URLs are included
2. **Given** the sitemap, **When** validating its structure, **Then** it conforms to the sitemap protocol standard
3. **Given** a new post is published, **When** the sitemap is regenerated, **Then** the new post URL is included
4. **Given** a post is unpublished, **When** the sitemap is regenerated, **Then** that URL is removed

---

### User Story 2 - Include All Content Types (Priority: P1)

The sitemap needs to include posts, pages, categories, tags, and author pages for complete coverage.

**Why this priority**: Comprehensive sitemap ensures all content is discoverable by search engines.

**Independent Test**: Can be tested by verifying each content type appears in the sitemap.

**Acceptance Scenarios**:

1. **Given** published pages exist, **When** viewing sitemap, **Then** all page URLs are included
2. **Given** categories with posts, **When** viewing sitemap, **Then** category archive URLs are included
3. **Given** tags with posts, **When** viewing sitemap, **Then** tag archive URLs are included
4. **Given** authors with posts, **When** viewing sitemap, **Then** author page URLs are included

---

### User Story 3 - Include Sitemap Metadata (Priority: P2)

Each sitemap entry needs metadata to help search engines prioritize and schedule crawling.

**Why this priority**: Metadata improves crawl efficiency but sitemap works without it.

**Independent Test**: Can be tested by checking that entries include lastmod and changefreq values.

**Acceptance Scenarios**:

1. **Given** a sitemap entry, **When** viewing its XML, **Then** it includes lastmod with the content's last modification date
2. **Given** post entries, **When** viewing sitemap, **Then** they have appropriate changefreq values
3. **Given** the homepage entry, **When** viewing sitemap, **Then** it has highest priority value
4. **Given** archive pages, **When** viewing sitemap, **Then** they have lower priority than individual posts

---

### User Story 4 - Submit Sitemap to Search Engines (Priority: P3)

The sitemap URL needs to be accessible and communicated to search engines.

**Why this priority**: While important, search engines can also discover sitemap from robots.txt.

**Independent Test**: Can be tested by verifying sitemap URL is in robots.txt and accessible.

**Acceptance Scenarios**:

1. **Given** robots.txt, **When** viewing it, **Then** it includes the sitemap URL reference
2. **Given** the sitemap URL, **When** search engine bots access it, **Then** it returns valid XML with 200 status
3. **Given** sitemap index, **When** site has many URLs, **Then** multiple sitemap files are generated and indexed

---

### Edge Cases

- What happens when there are more than 50,000 URLs? (Split into multiple sitemap files with index)
- What happens when sitemap file exceeds 50MB? (Compress with gzip)
- What happens when a URL contains special characters? (Properly encode for XML)
- What happens when content has no last modification date? (Use creation date)
- How does system handle draft or private content? (Exclude from sitemap)

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: System MUST generate valid XML sitemap at /sitemap.xml
- **FR-002**: System MUST include all published post URLs in sitemap
- **FR-003**: System MUST include all published page URLs in sitemap
- **FR-004**: System MUST include category archive URLs in sitemap
- **FR-005**: System MUST include tag archive URLs in sitemap
- **FR-006**: System MUST include author page URLs in sitemap
- **FR-007**: System MUST include lastmod date for each entry
- **FR-008**: System MUST include changefreq hint for each entry
- **FR-009**: System MUST include priority value for each entry
- **FR-010**: System MUST exclude unpublished, draft, and private content
- **FR-011**: System MUST properly encode URLs for XML compatibility
- **FR-012**: System MUST split sitemap into multiple files when exceeding 50,000 URLs
- **FR-013**: System MUST reference sitemap URL in robots.txt
- **FR-014**: System MUST update sitemap when content is published or modified
- **FR-015**: System SHOULD support image sitemap extension for featured images

### Key Entities

- **Sitemap Index**: Master file listing all individual sitemap files
- **Sitemap File**: XML file containing up to 50,000 URL entries with metadata

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: Sitemap generates in under 5 seconds for sites with up to 10,000 URLs
- **SC-002**: 100% of published content URLs are included in sitemap
- **SC-003**: Sitemap passes validation against sitemap protocol standards
- **SC-004**: Sitemap updates reflect content changes within 5 minutes
- **SC-005**: Sitemap is accessible and returns 200 status code 100% of the time
- **SC-006**: Search engines can successfully parse and crawl from generated sitemap

## Assumptions

- The blog follows standard URL patterns for all content types
- Content modification timestamps are accurately tracked in the database
- Sitemap generation can be cached and regenerated on content changes
- The server can handle sitemap requests without performance impact
