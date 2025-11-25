# Research: Static Pages

## Decision Log

### 1. Page Model Structure

**Decision**: Create Page model mirroring Post model structure with additional hierarchy support

**Rationale**:
- Consistency with existing codebase patterns
- Reuse of proven patterns (SEO fields, status management, soft deletes)
- Easier maintenance with familiar structure

**Alternatives Considered**:
- Single polymorphic "Content" model: Rejected due to added complexity
- Extending Post model: Rejected as pages have different requirements (no categories/tags)

### 2. Hierarchical URL Structure

**Decision**: Use recursive slug building with parent chain (e.g., /services/consulting)

**Rationale**:
- SEO-friendly URLs reflecting page hierarchy
- Standard approach used by WordPress and other CMS
- Easy to implement with Eloquent relationships

**Alternatives Considered**:
- Flat URLs with prefix: Rejected as less intuitive
- Nested set model: Overkill for 3-level hierarchy

### 3. Template System

**Decision**: Use configurable Blade templates with template selector in admin

**Rationale**:
- Laravel's native templating system
- Easy to add new templates
- No additional dependencies needed

**Alternatives Considered**:
- Database-stored templates: Too complex, harder to version control
- Component-based builder: Overkill for static pages

### 4. Slug Conflict Prevention

**Decision**: Check against posts, pages, and reserved routes during validation

**Rationale**:
- Prevents 404s and routing conflicts
- Required by spec FR-009
- Simple validation rule implementation

**Reserved Slugs**: admin, api, login, logout, register, dashboard, feed, sitemap

### 5. Scheduled Publishing

**Decision**: Use Laravel's scheduler with a custom command

**Rationale**:
- Consistent with existing Laravel patterns
- Posts already use similar scheduling approach
- Reliable and testable

**Alternatives Considered**:
- Queue-based delayed publishing: More complex, less predictable timing
