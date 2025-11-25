# Research: Widgets & Sidebar

## Decision Log

### 1. Widget Architecture

**Decision**: PHP classes extending BaseWidget with config array stored in DB

**Rationale**: Simple, type-safe, easy to add new widgets.

### 2. Widget Areas

**Decision**: Predefined areas in config (primary_sidebar, footer_1, footer_2, footer_3)

**Rationale**: Fixed areas are sufficient, extensible via config file.

### 3. Widget Configuration Storage

**Decision**: JSON column in widget_instances table

**Rationale**: Flexible schema for different widget settings, SQLite compatible.

### 4. Caching Strategy

**Decision**: Cache entire widget area output, tagged cache for invalidation

**Rationale**: Widgets rarely change, significant performance benefit.
