# Research: Post Revisions

## Decision Log

### 1. Storage Approach
**Decision**: Full snapshot per revision (not diff-based)
**Rationale**: Simpler implementation, instant restore, acceptable storage overhead.

### 2. Diff Library
**Decision**: jfcherng/php-diff
**Rationale**: Modern PHP, side-by-side output, good Laravel integration.

### 3. Auto-Save
**Decision**: Livewire wire:poll or JS interval calling save endpoint
**Rationale**: Native Livewire feature, no additional dependencies.

### 4. Revision Limit
**Decision**: Configurable limit (default 25), oldest pruned automatically
**Rationale**: Prevents unbounded storage growth.
