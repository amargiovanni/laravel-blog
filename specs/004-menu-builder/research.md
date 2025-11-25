# Research: Menu Builder

## Decision Log

### 1. Menu Storage Structure

**Decision**: Two tables - `menus` (container) and `menu_items` (nested items with parent_id)

**Rationale**: Simple parent_id approach is sufficient for 3-level menus, easy to understand and maintain.

### 2. Drag-and-Drop Library

**Decision**: Use SortableJS via Alpine.js plugin

**Rationale**: Already used in Filament ecosystem, lightweight, works well with Livewire.

### 3. Menu Locations

**Decision**: Store locations as enum in `menus` table with predefined options

**Rationale**: Fixed locations (header, footer, mobile) are sufficient, extensible via config if needed.

### 4. Item Types

**Decision**: Polymorphic `linkable_type` and `linkable_id` for pages/posts/categories, plain URL for custom links

**Rationale**: Flexible, allows auto-update when linked content changes.

### 5. Caching Strategy

**Decision**: Cache rendered menu HTML per location, invalidate on menu/item changes

**Rationale**: Menus rarely change, caching improves frontend performance significantly.
