# Quickstart: Widgets & Sidebar

## Setup Steps

```bash
php artisan make:migration create_widget_instances_table
php artisan make:model WidgetInstance
# Create widget classes manually in app/Widgets/
```

## Usage

```blade
<x-widget-area area="primary_sidebar" />
<x-widget-area area="footer_1" />
```
