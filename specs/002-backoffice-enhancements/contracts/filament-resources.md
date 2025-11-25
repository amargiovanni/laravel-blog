# Filament Resources Contract

**Feature**: 002-backoffice-enhancements
**Date**: 2025-11-25

This document defines the interface contracts for Filament Resources in the backoffice.

## CategoryResource

### Navigation
- **Group**: Content
- **Icon**: heroicon-o-folder
- **Sort**: 2 (after Posts)
- **Label**: Categories

### Form Schema
```
Section: "Category Details"
├── TextInput: name (required, max:100, live)
├── TextInput: slug (required, alpha_dash, unique)
├── Textarea: description (nullable, rows:3)
├── Select: parent_id
│   └── relationship: parent, titleAttribute: name
│   └── searchable, preload
│   └── exclude self from options
└── TextInput: sort_order (numeric, default:0)
```

### Table Columns
| Column | Type | Sortable | Searchable |
|--------|------|----------|------------|
| name | TextColumn | Yes | Yes |
| slug | TextColumn | No | Yes |
| parent.name | TextColumn | Yes | No |
| posts_count | TextColumn | Yes | No |
| created_at | TextColumn | Yes | No |

### Filters
- parent_id (SelectFilter → roots only option)
- has_posts (TernaryFilter)

### Actions
- View, Edit, Delete (standard)
- Delete: blocked if has children or posts (unless force)

### Bulk Actions
- Delete (with confirmation, blocked if any has children/posts)

---

## TagResource

### Navigation
- **Group**: Content
- **Icon**: heroicon-o-tag
- **Sort**: 3 (after Categories)
- **Label**: Tags

### Form Schema
```
Section: "Tag Details"
├── TextInput: name (required, max:100, live)
└── TextInput: slug (required, alpha_dash, unique)
```

### Table Columns
| Column | Type | Sortable | Searchable |
|--------|------|----------|------------|
| name | TextColumn | Yes | Yes |
| slug | TextColumn | No | Yes |
| posts_count | TextColumn | Yes | No |
| created_at | TextColumn | Yes | No |

### Filters
- has_posts (TernaryFilter)

### Actions
- View, Edit, Delete (standard)

### Bulk Actions
- Delete (standard)
- Merge (custom action)
  - Opens modal with target tag select
  - Moves all posts to target, deletes source tags

---

## MediaResource

### Navigation
- **Group**: Content
- **Icon**: heroicon-o-photo
- **Sort**: 4
- **Label**: Media Library

### Form Schema (Create/Edit Modal)
```
Section: "Upload"
├── FileUpload: file (required on create)
│   └── disk: public
│   └── directory: media/{year}/{month}
│   └── acceptedFileTypes: image/*, application/pdf
│   └── maxSize: 10240 (10MB)

Section: "Details"
├── TextInput: alt (max:255)
├── TextInput: title (max:255)
└── Textarea: caption (nullable)
```

### Table/Grid View
Toggle between table and grid view (grid default)

**Grid Card**:
- Thumbnail image (or file icon for PDF)
- Filename
- File size
- Usage badge (if used)

**Table Columns**:
| Column | Type | Sortable | Searchable |
|--------|------|----------|------------|
| thumbnail | ImageColumn | No | No |
| name | TextColumn | Yes | Yes |
| mime_type | BadgeColumn | No | No |
| size (formatted) | TextColumn | Yes | No |
| usage_count | TextColumn | Yes | No |
| uploader.name | TextColumn | Yes | No |
| created_at | TextColumn | Yes | No |

### Filters
- type (SelectFilter: images, documents)
- is_used (TernaryFilter)
- uploaded_by (SelectFilter → users)

### Actions
- View (opens details modal)
- Edit (metadata only)
- Delete (blocked if in use, unless force)

### Bulk Actions
- Delete unused only (filters to unused, then deletes)

---

## UserResource

### Navigation
- **Group**: System
- **Icon**: heroicon-o-users
- **Sort**: 1
- **Label**: Users

### Form Schema
```
Section: "Account"
├── TextInput: name (required, max:255)
├── TextInput: email (required, email, unique)
├── TextInput: password (required on create, hidden on edit)
│   └── dehydrateStateUsing: Hash::make()
│   └── confirmed
└── Toggle: is_active (default: true)

Section: "Profile"
├── FileUpload: avatar
│   └── image, avatar mode
├── Textarea: bio (nullable)

Section: "Roles"
└── CheckboxList: roles
    └── relationship: roles
    └── options from Role::all()
```

### Table Columns
| Column | Type | Sortable | Searchable |
|--------|------|----------|------------|
| avatar | ImageColumn | No | No |
| name | TextColumn | Yes | Yes |
| email | TextColumn | Yes | Yes |
| roles.name | BadgeColumn | No | No |
| is_active | IconColumn | Yes | No |
| posts_count | TextColumn | Yes | No |
| created_at | TextColumn | Yes | No |

### Filters
- role (SelectFilter)
- is_active (TernaryFilter)
- email_verified (TernaryFilter)

### Actions
- View, Edit
- Deactivate/Activate (toggle action)
- Delete: blocked if last admin or has posts (unless reassigned)
- Impersonate (optional, admin only)

### Bulk Actions
- Deactivate
- Activate
- Assign Role

---

## ThemeSettings (Filament Page)

### Navigation
- **Group**: Settings
- **Icon**: heroicon-o-paint-brush
- **Label**: Theme

### Form Schema
```
Section: "Colors"
├── ColorPicker: theme.primary_color (default: #3B82F6)
└── ColorPicker: theme.secondary_color (default: #10B981)

Section: "Branding"
├── FileUpload: theme.logo_path
│   └── image, max:2MB
├── FileUpload: theme.favicon_path
│   └── image, max:1MB, acceptedFileTypes: image/x-icon,image/png

Section: "Footer"
├── TextInput: theme.footer_text
├── Repeater: theme.footer_links
│   ├── TextInput: label
│   └── TextInput: url
└── Repeater: theme.social_links
    ├── Select: platform (twitter, facebook, instagram, linkedin, youtube, github)
    └── TextInput: url
```

### Actions
- Save (default)
- Reset to Defaults (custom action with confirmation)

---

## GeoSettings (Filament Page)

### Navigation
- **Group**: Settings
- **Icon**: heroicon-o-globe-alt
- **Label**: SEO & AI

### Form Schema
```
Section: "Site Information"
├── TextInput: geo.site_name (default: config('app.name'))
└── Textarea: geo.site_description

Section: "llms.txt Configuration"
├── Toggle: geo.llms_enabled (default: true)
├── Toggle: geo.llms_include_posts
├── Toggle: geo.llms_include_categories
└── Placeholder: llms.txt preview (live updated)

Section: "Structured Data"
└── Toggle: geo.jsonld_enabled (default: true)

Section: "Status"
└── View: Dashboard showing
    - llms.txt URL with copy button
    - Last generated timestamp
    - Validation status
```

### Actions
- Save
- Regenerate llms.txt (manual trigger)
- Validate JSON-LD (opens test link)

---

## Policy Contracts

All resources require policies with these methods:

```php
interface ResourcePolicy
{
    public function viewAny(User $user): bool;
    public function view(User $user, Model $model): bool;
    public function create(User $user): bool;
    public function update(User $user, Model $model): bool;
    public function delete(User $user, Model $model): bool;
    public function deleteAny(User $user): bool;
    public function forceDelete(User $user, Model $model): bool;
    public function forceDeleteAny(User $user): bool;
    public function restore(User $user, Model $model): bool;
    public function restoreAny(User $user): bool;
}
```

### Permission Mapping

| Resource | View | Create | Update | Delete |
|----------|------|--------|--------|--------|
| Category | view categories | create categories | update categories | delete categories |
| Tag | view tags | create tags | update tags | delete tags |
| Media | view media | upload media | update media | delete media |
| User | view users | create users | update users | delete users |

**Roles**:
- **admin**: All permissions
- **editor**: categories, tags, media (all), users (view only)
- **author**: categories (view), tags (view), media (upload own, view all)
- **subscriber**: None (no admin access)
