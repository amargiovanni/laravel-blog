# Data Model: Widgets & Sidebar

## Entities

### WidgetInstance

| Field | Type | Constraints | Description |
|-------|------|-------------|-------------|
| id | bigint | PK | Primary key |
| area | string(50) | required | Widget area identifier |
| widget_type | string(100) | required | Widget class name |
| title | string(100) | nullable | Widget title |
| settings | json | nullable | Widget configuration |
| sort_order | integer | default: 0 | Display order |
| created_at | datetime | auto | |
| updated_at | datetime | auto | |

### Widget Areas (Config-based)

```php
// config/widgets.php
'areas' => [
    'primary_sidebar' => 'Primary Sidebar',
    'footer_1' => 'Footer Column 1',
    'footer_2' => 'Footer Column 2',
    'footer_3' => 'Footer Column 3',
]
```

### Widget Types (Class-based)

```php
// Available widgets
App\Widgets\SearchWidget
App\Widgets\RecentPostsWidget
App\Widgets\CategoriesWidget
App\Widgets\TagsWidget
App\Widgets\ArchivesWidget
App\Widgets\CustomHtmlWidget
```
