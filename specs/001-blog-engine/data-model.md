# Data Model: Laravel Blog Engine

**Feature**: 001-blog-engine
**Date**: 2025-11-25

## Entity Relationship Diagram

```
┌─────────────┐       ┌─────────────┐       ┌─────────────┐
│    User     │       │    Post     │       │  Category   │
├─────────────┤       ├─────────────┤       ├─────────────┤
│ id          │──┐    │ id          │    ┌──│ id          │
│ name        │  │    │ title       │    │  │ name        │
│ email       │  │    │ slug        │    │  │ slug        │
│ password    │  │    │ content     │    │  │ description │
│ avatar      │  └───▶│ author_id   │    │  │ parent_id   │──┐
│ bio         │       │ excerpt     │    │  └─────────────┘  │
│ theme_pref  │       │ status      │    │        ▲          │
└─────────────┘       │ published_at│    │        └──────────┘
      │               │ featured_img│    │        (self-ref)
      │               └─────────────┘    │
      │                     │            │
      ▼                     │            │
┌─────────────┐            │            │
│   Comment   │            │            │
├─────────────┤            ▼            ▼
│ id          │       ┌─────────────────────┐
│ post_id     │──────▶│   category_post     │
│ user_id     │       │ (pivot)             │
│ parent_id   │──┐    └─────────────────────┘
│ author_name │  │
│ author_email│  │          ┌─────────────┐
│ content     │  │          │    Tag      │
│ status      │  │          ├─────────────┤
└─────────────┘  │          │ id          │
      ▲          │          │ name        │
      └──────────┘          │ slug        │
      (self-ref)            └─────────────┘
                                  │
                                  ▼
                            ┌─────────────────────┐
                            │     post_tag        │
                            │ (pivot)             │
                            └─────────────────────┘

┌─────────────┐       ┌─────────────┐       ┌─────────────┐
│   Media     │       │  Activity   │       │  Setting    │
├─────────────┤       ├─────────────┤       ├─────────────┤
│ id          │       │ id          │       │ id          │
│ name        │       │ log_name    │       │ key         │
│ path        │       │ description │       │ value       │
│ disk        │       │ subject_type│       │ group       │
│ mime_type   │       │ subject_id  │       └─────────────┘
│ size        │       │ causer_type │
│ alt         │       │ causer_id   │
│ title       │       │ properties  │
│ caption     │       │ created_at  │
│ sizes       │       └─────────────┘
└─────────────┘
```

---

## Entity Definitions

### User

Represents authenticated users with role-based access.

| Field | Type | Constraints | Description |
|-------|------|-------------|-------------|
| id | bigint | PK, auto | Primary key |
| name | string(255) | required | Display name |
| email | string(255) | required, unique | Login email |
| email_verified_at | timestamp | nullable | Verification timestamp |
| password | string(255) | required | Hashed password |
| avatar | string(255) | nullable | Avatar image path |
| bio | text | nullable | Author biography |
| theme_preference | enum | default: 'system' | 'light', 'dark', 'system' |
| remember_token | string(100) | nullable | Session token |
| created_at | timestamp | auto | Creation timestamp |
| updated_at | timestamp | auto | Update timestamp |

**Relationships**:
- `hasMany` Post (as author)
- `hasMany` Comment
- `belongsToMany` Role (via Spatie Permission)
- `hasMany` Activity (via Spatie Activitylog)

**Indexes**:
- `email` (unique)

---

### Post

Blog content with publishing workflow.

| Field | Type | Constraints | Description |
|-------|------|-------------|-------------|
| id | bigint | PK, auto | Primary key |
| title | string(255) | required | Post title |
| slug | string(255) | required, unique | URL-friendly identifier |
| content | longtext | required | Post body (HTML) |
| excerpt | text | nullable | Short summary |
| author_id | bigint | FK → users.id | Author reference |
| status | enum | default: 'draft' | 'draft', 'scheduled', 'published' |
| published_at | timestamp | nullable | Publication date/time |
| featured_image_id | bigint | FK → media.id, nullable | Featured image |
| meta_title | string(60) | nullable | SEO title override |
| meta_description | string(160) | nullable | SEO description |
| focus_keyword | string(100) | nullable | SEO focus keyword |
| allow_comments | boolean | default: true | Comments enabled |
| view_count | bigint | default: 0 | Page views |
| created_at | timestamp | auto | Creation timestamp |
| updated_at | timestamp | auto | Update timestamp |
| deleted_at | timestamp | nullable | Soft delete |

**Relationships**:
- `belongsTo` User (as author)
- `belongsTo` Media (as featured_image)
- `belongsToMany` Category (via category_post)
- `belongsToMany` Tag (via post_tag)
- `hasMany` Comment
- `morphMany` Activity

**Indexes**:
- `slug` (unique)
- `status, published_at` (compound for queries)
- `author_id` (foreign key)
- `FULLTEXT(title, content, excerpt)` (search)

**State Transitions**:
```
draft ──────▶ scheduled ──────▶ published
  │              │                  │
  └──────────────┴──────────────────┘
         (can revert to draft)
```

---

### Category

Hierarchical content organization.

| Field | Type | Constraints | Description |
|-------|------|-------------|-------------|
| id | bigint | PK, auto | Primary key |
| name | string(255) | required | Category name |
| slug | string(255) | required, unique | URL-friendly identifier |
| description | text | nullable | Category description |
| parent_id | bigint | FK → categories.id, nullable | Parent category |
| sort_order | int | default: 0 | Display order |
| created_at | timestamp | auto | Creation timestamp |
| updated_at | timestamp | auto | Update timestamp |

**Relationships**:
- `belongsTo` Category (as parent)
- `hasMany` Category (as children)
- `belongsToMany` Post (via category_post)

**Indexes**:
- `slug` (unique)
- `parent_id` (foreign key)

**Validation Rules**:
- Cannot be its own parent
- Maximum nesting depth: 5 levels

---

### Tag

Flat content labels for cross-category discovery.

| Field | Type | Constraints | Description |
|-------|------|-------------|-------------|
| id | bigint | PK, auto | Primary key |
| name | string(100) | required | Tag name |
| slug | string(100) | required, unique | URL-friendly identifier |
| created_at | timestamp | auto | Creation timestamp |
| updated_at | timestamp | auto | Update timestamp |

**Relationships**:
- `belongsToMany` Post (via post_tag)

**Indexes**:
- `slug` (unique)
- `name` (for autocomplete queries)

---

### Comment

Reader feedback with moderation workflow.

| Field | Type | Constraints | Description |
|-------|------|-------------|-------------|
| id | bigint | PK, auto | Primary key |
| post_id | bigint | FK → posts.id | Parent post |
| user_id | bigint | FK → users.id, nullable | Authenticated commenter |
| parent_id | bigint | FK → comments.id, nullable | Parent comment (threading) |
| author_name | string(255) | required if guest | Guest commenter name |
| author_email | string(255) | required if guest | Guest commenter email |
| content | text | required | Comment body |
| status | enum | default: 'pending' | 'pending', 'approved', 'rejected', 'spam' |
| ip_address | string(45) | nullable | IPv4/IPv6 address |
| user_agent | string(500) | nullable | Browser user agent |
| created_at | timestamp | auto | Creation timestamp |
| updated_at | timestamp | auto | Update timestamp |

**Relationships**:
- `belongsTo` Post
- `belongsTo` User (nullable)
- `belongsTo` Comment (as parent)
- `hasMany` Comment (as replies)

**Indexes**:
- `post_id, status` (compound for display queries)
- `status` (for moderation queue)
- `parent_id` (foreign key)

**Validation Rules**:
- Maximum reply depth: 2 levels
- Max 3 comments per minute per IP

**State Transitions**:
```
pending ──────▶ approved
    │
    └──────────▶ rejected
    │
    └──────────▶ spam
```

---

### Media

Uploaded files with generated sizes.

| Field | Type | Constraints | Description |
|-------|------|-------------|-------------|
| id | bigint | PK, auto | Primary key |
| name | string(255) | required | Original filename |
| path | string(500) | required | Storage path |
| disk | string(50) | default: 'public' | Storage disk |
| mime_type | string(100) | required | MIME type |
| size | bigint | required | File size in bytes |
| alt | string(255) | nullable | Alt text |
| title | string(255) | nullable | Title attribute |
| caption | text | nullable | Caption/description |
| sizes | json | nullable | Generated size paths |
| uploaded_by | bigint | FK → users.id | Uploader reference |
| created_at | timestamp | auto | Creation timestamp |
| updated_at | timestamp | auto | Update timestamp |

**Relationships**:
- `belongsTo` User (as uploader)
- `hasMany` Post (as featured_image)

**Indexes**:
- `mime_type` (for filtering)
- `uploaded_by` (foreign key)

**Generated Sizes** (JSON structure):
```json
{
  "thumbnail": "media/001/thumb_image.webp",
  "medium": "media/001/medium_image.webp",
  "large": "media/001/large_image.webp"
}
```

---

### Setting

System-wide configuration key-value pairs.

| Field | Type | Constraints | Description |
|-------|------|-------------|-------------|
| id | bigint | PK, auto | Primary key |
| key | string(100) | required, unique | Setting identifier |
| value | text | nullable | Setting value (JSON-encoded for complex) |
| group | string(50) | default: 'general' | Setting group |
| created_at | timestamp | auto | Creation timestamp |
| updated_at | timestamp | auto | Update timestamp |

**Indexes**:
- `key` (unique)
- `group` (for grouped queries)

**Common Settings**:
```
general.site_name
general.site_description
general.active_theme
comments.require_moderation
comments.allow_guest
seo.default_meta_title
seo.default_meta_description
```

---

## Pivot Tables

### category_post

| Field | Type | Constraints |
|-------|------|-------------|
| category_id | bigint | FK → categories.id |
| post_id | bigint | FK → posts.id |

**Indexes**: Primary key on (category_id, post_id)

### post_tag

| Field | Type | Constraints |
|-------|------|-------------|
| post_id | bigint | FK → posts.id |
| tag_id | bigint | FK → tags.id |

**Indexes**: Primary key on (post_id, tag_id)

---

## External Tables (via Packages)

### Spatie Permission Tables

- `roles` - Role definitions
- `permissions` - Permission definitions
- `model_has_roles` - User-role assignments
- `model_has_permissions` - Direct permission assignments
- `role_has_permissions` - Role-permission assignments

### Spatie Activitylog Tables

- `activity_log` - Activity entries with JSON properties

---

## Migration Order

1. `users` (base table)
2. `categories` (self-referencing)
3. `tags` (independent)
4. `media` (depends on users)
5. `posts` (depends on users, media)
6. `category_post` (pivot)
7. `post_tag` (pivot)
8. `comments` (depends on posts, users, self-referencing)
9. `settings` (independent)
10. Spatie Permission migrations
11. Spatie Activitylog migrations

---

## Soft Deletes

Enabled on:
- `posts` - Content recovery
- `comments` - Moderation history

Not enabled on:
- `users` - Use status flag instead for GDPR compliance
- `categories` - Reassign posts before deletion
- `tags` - Remove pivot entries before deletion
- `media` - Clean up files on delete
