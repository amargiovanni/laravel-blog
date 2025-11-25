# Routes Contract: Static Pages

## Admin Routes (Filament)

Handled automatically by Filament PageResource:

| Method | URI | Name | Description |
|--------|-----|------|-------------|
| GET | /admin/pages | filament.admin.resources.pages.index | List pages |
| GET | /admin/pages/create | filament.admin.resources.pages.create | Create form |
| POST | /admin/pages | filament.admin.resources.pages.store | Store page |
| GET | /admin/pages/{record} | filament.admin.resources.pages.view | View page |
| GET | /admin/pages/{record}/edit | filament.admin.resources.pages.edit | Edit form |
| PUT | /admin/pages/{record} | filament.admin.resources.pages.update | Update page |
| DELETE | /admin/pages/{record} | filament.admin.resources.pages.destroy | Delete page |

## Frontend Routes

| Method | URI | Name | Description |
|--------|-----|------|-------------|
| GET | /{slug} | pages.show | Show root-level page |
| GET | /{parent}/{slug} | pages.show.nested | Show nested page (1 level) |
| GET | /{grandparent}/{parent}/{slug} | pages.show.deeply-nested | Show nested page (2 levels) |

**Note**: Page routes must be registered AFTER all other routes to act as catch-all for static pages. The controller should return 404 if no matching page is found.

## Route Resolution Logic

```
1. Check if slug matches a published page at root level
2. If not found, check if it's a nested path (parent/child)
3. If not found, check if it's a deeply nested path (gp/parent/child)
4. If no match, return 404
```
