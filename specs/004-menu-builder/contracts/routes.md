# Routes Contract: Menu Builder

## Admin Routes (Filament)

| Method | URI | Description |
|--------|-----|-------------|
| GET | /admin/menus | List menus |
| GET | /admin/menus/create | Create menu |
| GET | /admin/menus/{record}/edit | Edit menu with items |
| POST | /admin/menus/{record}/reorder | Reorder items (AJAX) |

## No Frontend Routes

Menus are rendered via Blade component:
```blade
<x-navigation location="header" />
<x-navigation location="footer" />
```
