# Data Model: Menu Builder

## Entities

### Menu

| Field | Type | Constraints | Description |
|-------|------|-------------|-------------|
| id | bigint | PK | Primary key |
| name | string(100) | required | Menu name |
| location | enum | header,footer,mobile,none | Display location |
| created_at | datetime | auto | Creation timestamp |
| updated_at | datetime | auto | Update timestamp |

### MenuItem

| Field | Type | Constraints | Description |
|-------|------|-------------|-------------|
| id | bigint | PK | Primary key |
| menu_id | bigint | FK → menus.id | Parent menu |
| parent_id | bigint | nullable, FK → menu_items.id | Parent item (for nesting) |
| label | string(100) | required | Display text |
| linkable_type | string | nullable | Polymorphic type (Page, Post, Category) |
| linkable_id | bigint | nullable | Polymorphic ID |
| url | string(500) | nullable | Custom URL (if not linkable) |
| target | enum | _self, _blank | Link target |
| css_class | string(100) | nullable | Custom CSS classes |
| sort_order | integer | default: 0 | Display order |
| created_at | datetime | auto | Creation timestamp |
| updated_at | datetime | auto | Update timestamp |

### Relationships

```
Menu hasMany MenuItem
MenuItem belongsTo Menu
MenuItem belongsTo MenuItem (parent) - nullable
MenuItem hasMany MenuItem (children)
MenuItem morphsTo linkable (Page, Post, Category)
```

### Indexes

- `menu_items_menu_id_index` on menu_id
- `menu_items_parent_id_index` on parent_id
- `menus_location_index` on location
