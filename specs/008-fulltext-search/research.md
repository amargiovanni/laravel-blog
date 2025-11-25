# Research: Full-Text Search

## Decisions

### Search Driver
**Decision**: Laravel Scout with database driver (dev), Meilisearch (prod)
**Rationale**: Zero dependencies for dev, scalable for production.

### Searchable Models
**Decision**: Post, Page - add Searchable trait
**Rationale**: Primary content types, categories/tags via relationships.

### Highlighting
**Decision**: Custom excerpt generator with term highlighting
**Rationale**: Better UX, shows context around matches.
