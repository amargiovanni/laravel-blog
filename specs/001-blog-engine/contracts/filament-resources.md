# Filament Resource Contracts: Laravel Blog Engine

**Feature**: 001-blog-engine
**Date**: 2025-11-25

This document defines the Filament admin panel resource specifications.

---

## PostResource

**Model**: `App\Models\Post`
**Navigation**: Posts
**Icon**: `heroicon-o-document-text`

### Table Columns

| Column | Type | Sortable | Searchable |
|--------|------|----------|------------|
| featured_image | ImageColumn | No | No |
| title | TextColumn | Yes | Yes |
| author.name | TextColumn | Yes | Yes |
| status | BadgeColumn | Yes | No |
| categories | TextColumn (count) | No | No |
| published_at | TextColumn (date) | Yes | No |
| view_count | TextColumn | Yes | No |
| created_at | TextColumn (date) | Yes | No |

### Table Filters

- Status (draft, scheduled, published)
- Author (select)
- Category (select)
- Date range (published_at)

### Table Actions

- Edit
- View
- Delete
- Bulk delete
- Bulk publish
- Bulk unpublish

### Form Schema

```
Section: Content
├── TextInput: title (required, max:255)
├── TextInput: slug (required, unique, auto-generated)
├── RichEditor: content (required)
├── Textarea: excerpt (max:500)
└── FileUpload: featured_image (image, max:5MB)

Section: Organization
├── Select: categories (multiple, relationship)
├── TagsInput: tags (createable)
└── Select: author_id (relationship, default:auth)

Section: Publishing
├── Select: status (draft, scheduled, published)
├── DateTimePicker: published_at (required if scheduled/published)
└── Toggle: allow_comments (default:true)

Section: SEO
├── TextInput: meta_title (max:60)
├── Textarea: meta_description (max:160)
└── TextInput: focus_keyword (max:100)
```

### Authorization

- viewAny: admin, editor, author
- view: admin, editor, author (own)
- create: admin, editor, author
- update: admin, editor, author (own)
- delete: admin, editor
- bulkDelete: admin, editor

---

## CategoryResource

**Model**: `App\Models\Category`
**Navigation**: Categories
**Icon**: `heroicon-o-folder`

### Table Columns

| Column | Type | Sortable | Searchable |
|--------|------|----------|------------|
| name | TextColumn | Yes | Yes |
| slug | TextColumn | No | Yes |
| parent.name | TextColumn | Yes | No |
| posts_count | TextColumn | Yes | No |
| sort_order | TextColumn | Yes | No |

### Table Filters

- Has parent (boolean)

### Table Actions

- Edit
- Delete
- Reorder (drag-drop)

### Form Schema

```
Section: Category
├── TextInput: name (required, max:255)
├── TextInput: slug (required, unique, auto-generated)
├── Textarea: description
├── Select: parent_id (relationship, nullable, exclude self)
└── TextInput: sort_order (numeric, default:0)
```

### Authorization

- viewAny: admin, editor
- view: admin, editor
- create: admin, editor
- update: admin, editor
- delete: admin (only if no posts)

---

## TagResource

**Model**: `App\Models\Tag`
**Navigation**: Tags
**Icon**: `heroicon-o-tag`

### Table Columns

| Column | Type | Sortable | Searchable |
|--------|------|----------|------------|
| name | TextColumn | Yes | Yes |
| slug | TextColumn | No | Yes |
| posts_count | TextColumn | Yes | No |
| created_at | TextColumn | Yes | No |

### Table Actions

- Edit
- Delete
- Merge (custom action)

### Form Schema

```
Section: Tag
├── TextInput: name (required, max:100)
└── TextInput: slug (required, unique, auto-generated)
```

### Authorization

- viewAny: admin, editor, author
- view: admin, editor, author
- create: admin, editor, author
- update: admin, editor
- delete: admin, editor (only if no posts)

---

## CommentResource

**Model**: `App\Models\Comment`
**Navigation**: Comments
**Icon**: `heroicon-o-chat-bubble-left-right`

### Table Columns

| Column | Type | Sortable | Searchable |
|--------|------|----------|------------|
| post.title | TextColumn | Yes | Yes |
| author_display | TextColumn | No | Yes |
| content | TextColumn (limit:50) | No | Yes |
| status | BadgeColumn | Yes | No |
| created_at | TextColumn | Yes | No |

### Table Filters

- Status (pending, approved, rejected, spam)
- Has user (boolean - logged in vs guest)
- Post (select)

### Table Actions

- View
- Approve
- Reject
- Mark as spam
- Delete
- Bulk approve
- Bulk reject
- Bulk delete

### Form Schema (View/Edit)

```
Section: Comment
├── TextInput: author_name (disabled if user)
├── TextInput: author_email (disabled if user)
├── Placeholder: user.name (if user)
├── Textarea: content (required)
├── Select: status (pending, approved, rejected, spam)
└── Placeholder: post.title (link)

Section: Metadata
├── Placeholder: ip_address
├── Placeholder: user_agent
└── Placeholder: created_at
```

### Authorization

- viewAny: admin, editor
- view: admin, editor
- update: admin, editor
- delete: admin, editor

---

## UserResource

**Model**: `App\Models\User`
**Navigation**: Users
**Icon**: `heroicon-o-users`

### Table Columns

| Column | Type | Sortable | Searchable |
|--------|------|----------|------------|
| avatar | ImageColumn | No | No |
| name | TextColumn | Yes | Yes |
| email | TextColumn | Yes | Yes |
| roles | BadgeColumn | No | No |
| posts_count | TextColumn | Yes | No |
| email_verified_at | IconColumn (check/x) | Yes | No |
| created_at | TextColumn | Yes | No |

### Table Filters

- Role (select)
- Email verified (boolean)

### Table Actions

- Edit
- Delete
- Impersonate (admin only)
- Send verification email

### Form Schema

```
Section: User Information
├── FileUpload: avatar (image, avatar, max:2MB)
├── TextInput: name (required, max:255)
├── TextInput: email (required, email, unique)
├── Textarea: bio
└── Select: theme_preference (light, dark, system)

Section: Password
├── TextInput: password (password, confirmed, required on create)
└── TextInput: password_confirmation (password)

Section: Roles
└── CheckboxList: roles (relationship)
```

### Authorization

- viewAny: admin
- view: admin
- create: admin
- update: admin
- delete: admin (not self)

---

## MediaResource

**Model**: `App\Models\Media`
**Navigation**: Media
**Icon**: `heroicon-o-photo`

### Table Columns

| Column | Type | Sortable | Searchable |
|--------|------|----------|------------|
| thumbnail | ImageColumn | No | No |
| name | TextColumn | Yes | Yes |
| mime_type | BadgeColumn | Yes | No |
| size | TextColumn (formatted) | Yes | No |
| uploaded_by.name | TextColumn | Yes | No |
| created_at | TextColumn | Yes | No |

### Table Filters

- Type (image, document, video)
- Uploader (select)
- Date range

### Table Actions

- View
- Edit
- Delete
- Copy URL

### Form Schema

```
Section: Upload (Create only)
└── FileUpload: file (required, max:10MB)

Section: Details
├── TextInput: name (disabled, from filename)
├── TextInput: alt (max:255)
├── TextInput: title (max:255)
├── Textarea: caption
└── Placeholder: Generated sizes (thumbnails)

Section: Metadata
├── Placeholder: mime_type
├── Placeholder: size (formatted)
├── Placeholder: dimensions (if image)
└── Placeholder: path
```

### Authorization

- viewAny: admin, editor, author
- view: admin, editor, author
- create: admin, editor, author
- update: admin, editor, author (own)
- delete: admin, editor

---

## Dashboard Widgets

### StatsOverviewWidget

```
Stats:
├── Total Posts (with trend)
├── Published Posts
├── Total Comments (pending count badge)
├── Total Users
└── Total Views (this month)
```

### RecentActivityWidget

```
Table (last 10 activities):
├── User avatar
├── Description (e.g., "John created post 'Hello World'")
├── Subject link
└── Time ago
```

### TrendChartWidget

```
Line chart showing:
├── Posts published (by day/week)
├── Comments received (by day/week)
└── Page views (by day/week)
```

### LatestPostsWidget

```
Table (last 5 posts):
├── Title
├── Author
├── Status
└── Published date
```

### PendingCommentsWidget

```
Table (pending comments):
├── Post title
├── Author
├── Excerpt
└── Quick approve/reject actions
```

---

## Custom Pages

### ActivityLogPage

- Full activity log with filters
- Search by user, action, subject
- Date range filter
- Export to CSV

### SettingsPage

- General settings (site name, description, etc.)
- Comment settings (moderation, guest comments)
- SEO defaults
- Social media links

### ThemeSettingsPage

- Active theme selection
- Theme preview
- Basic customization (colors, fonts)
