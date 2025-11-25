# Data Model: Backoffice Enhancements

**Feature**: 002-backoffice-enhancements
**Date**: 2025-11-25

## Entity Overview

| Entity | Status | Changes |
|--------|--------|---------|
| Category | Existing | Add posts_count accessor |
| Tag | Existing | Add posts_count accessor |
| Media | Existing | Add usage tracking methods |
| User | Existing | Add is_active field |
| Setting | Existing | Add theme/geo groups |
| Role | Existing (Spatie) | Managed via Shield |
| Permission | Existing (Spatie) | Managed via Shield |

## Entity Details

### Category (Existing - Enhanced)

**Purpose**: Hierarchical taxonomy for organizing blog posts.

**Existing Fields**:
| Field | Type | Constraints |
|-------|------|-------------|
| id | bigint | PK, auto |
| name | string(100) | required |
| slug | string(100) | unique, required |
| description | text | nullable |
| parent_id | bigint | FK → categories.id, nullable |
| sort_order | integer | default 0 |
| created_at | timestamp | auto |
| updated_at | timestamp | auto |

**Relationships**:
- `parent()` → BelongsTo(Category)
- `children()` → HasMany(Category)
- `posts()` → BelongsToMany(Post)

**New Methods/Accessors**:
```php
// Accessor for posts count (for admin display)
public function getPostsCountAttribute(): int
{
    return $this->posts()->count();
}

// Check if can be deleted (no children, no posts or force)
public function canDelete(bool $force = false): bool
{
    if ($force) return true;
    return !$this->hasChildren() && $this->posts()->count() === 0;
}
```

**Validation Rules**:
- name: required, max:100
- slug: required, unique:categories,slug,{id}, alpha_dash, max:100
- parent_id: nullable, exists:categories,id, not self-referencing

---

### Tag (Existing - Enhanced)

**Purpose**: Flat taxonomy for labeling blog posts.

**Existing Fields**:
| Field | Type | Constraints |
|-------|------|-------------|
| id | bigint | PK, auto |
| name | string(100) | required |
| slug | string(100) | unique, required |
| created_at | timestamp | auto |
| updated_at | timestamp | auto |

**Relationships**:
- `posts()` → BelongsToMany(Post)

**New Methods/Accessors**:
```php
// Accessor for posts count
public function getPostsCountAttribute(): int
{
    return $this->posts()->count();
}

// Merge this tag into another (for bulk merge operation)
public function mergeInto(Tag $target): void
{
    $this->posts()->each(fn($post) => $post->tags()->syncWithoutDetaching($target->id));
    $this->posts()->detach();
    $this->delete();
}
```

**Validation Rules**:
- name: required, max:100
- slug: required, unique:tags,slug,{id}, alpha_dash, max:100

---

### Media (Existing - Enhanced)

**Purpose**: Uploaded media files with automatic size variants.

**Existing Fields**:
| Field | Type | Constraints |
|-------|------|-------------|
| id | bigint | PK, auto |
| name | string(255) | required |
| path | string(500) | required |
| disk | string(50) | default 'public' |
| mime_type | string(100) | required |
| size | bigint | bytes |
| alt | string(255) | nullable |
| title | string(255) | nullable |
| caption | text | nullable |
| sizes | json | nullable, image variants |
| uploaded_by | bigint | FK → users.id |
| created_at | timestamp | auto |
| updated_at | timestamp | auto |

**Relationships**:
- `uploader()` → BelongsTo(User)
- `featuredByPosts()` → HasMany(Post, 'featured_image_id')

**New Methods**:
```php
// Check if media is in use
public function isUsed(): bool
{
    return $this->featuredByPosts()->exists();
}

// Get usage count
public function getUsageCountAttribute(): int
{
    return $this->featuredByPosts()->count();
}

// Scope for unused media
public function scopeUnused(Builder $query): Builder
{
    return $query->whereDoesntHave('featuredByPosts');
}

// Scope by type
public function scopeImages(Builder $query): Builder
{
    return $query->where('mime_type', 'like', 'image/%');
}

public function scopeDocuments(Builder $query): Builder
{
    return $query->where('mime_type', 'like', 'application/%');
}
```

**Validation Rules (Upload)**:
- file: required, mimes:jpeg,png,gif,webp,pdf, max:10240 (10MB)
- alt: nullable, max:255
- title: nullable, max:255

---

### User (Existing - Enhanced)

**Purpose**: System users with authentication and role assignment.

**Existing Fields**:
| Field | Type | Constraints |
|-------|------|-------------|
| id | bigint | PK, auto |
| name | string(255) | required |
| email | string(255) | unique, required |
| email_verified_at | timestamp | nullable |
| password | string | required |
| avatar | string(500) | nullable |
| bio | text | nullable |
| remember_token | string(100) | nullable |
| created_at | timestamp | auto |
| updated_at | timestamp | auto |

**New Field (Migration Required)**:
| Field | Type | Constraints |
|-------|------|-------------|
| is_active | boolean | default true |

**Relationships**:
- `posts()` → HasMany(Post, 'author_id')
- `media()` → HasMany(Media, 'uploaded_by')
- `roles()` → BelongsToMany(Role) via Spatie

**New Methods**:
```php
// Check if user is the last admin
public function isLastAdmin(): bool
{
    if (!$this->hasRole('admin')) return false;
    return User::role('admin')->count() === 1;
}

// Deactivate user
public function deactivate(): void
{
    $this->update(['is_active' => false]);
}

// Reactivate user
public function activate(): void
{
    $this->update(['is_active' => true]);
}

// Scope for active users
public function scopeActive(Builder $query): Builder
{
    return $query->where('is_active', true);
}
```

**Validation Rules (Create)**:
- name: required, max:255
- email: required, email, unique:users
- password: required, min:8, confirmed
- role: required, exists:roles,name

**Validation Rules (Update)**:
- name: required, max:255
- email: required, email, unique:users,email,{id}
- password: nullable, min:8, confirmed
- role: required, exists:roles,name
- is_active: boolean

---

### Setting (Existing - Theme/GEO Usage)

**Purpose**: Key-value configuration storage with grouping.

**Existing Fields**:
| Field | Type | Constraints |
|-------|------|-------------|
| id | bigint | PK, auto |
| key | string(100) | unique, required |
| value | text | nullable |
| group | string(50) | default 'general' |
| created_at | timestamp | auto |
| updated_at | timestamp | auto |

**Theme Settings Keys**:
| Key | Type | Default | Description |
|-----|------|---------|-------------|
| theme.primary_color | hex | #3B82F6 | Primary brand color |
| theme.secondary_color | hex | #10B981 | Secondary accent color |
| theme.logo_path | string | null | Path to logo in storage |
| theme.favicon_path | string | null | Path to favicon in storage |
| theme.footer_text | string | © {year} Blog | Copyright text |
| theme.footer_links | json | [] | Array of {label, url} |
| theme.social_links | json | [] | Array of {platform, url} |

**GEO Settings Keys**:
| Key | Type | Default | Description |
|-----|------|---------|-------------|
| geo.llms_enabled | bool | true | Enable llms.txt generation |
| geo.llms_include_posts | bool | true | Include posts in llms.txt |
| geo.llms_include_categories | bool | true | Include categories |
| geo.jsonld_enabled | bool | true | Enable JSON-LD output |
| geo.site_name | string | config('app.name') | Site name for schema |
| geo.site_description | string | null | Site description |

**Existing Methods (verified)**:
- `Setting::get($key, $default)` - Cached retrieval
- `Setting::set($key, $value, $group)` - Create/update

---

## New Migration Required

### add_is_active_to_users_table

```php
Schema::table('users', function (Blueprint $table) {
    $table->boolean('is_active')->default(true)->after('bio');
});
```

---

## State Transitions

### User Status
```
Created → Active
Active → Deactivated (via deactivate())
Deactivated → Active (via activate())
Active/Deactivated → Deleted (if no posts or posts reassigned)
```

### Media Lifecycle
```
Uploaded → Processed (sizes generated)
Processed → In Use (attached to post)
In Use → Available (detached from posts)
Available → Deleted (bulk or individual)
```

---

## Indexes

No new indexes required. Existing indexes on:
- categories: slug (unique), parent_id
- tags: slug (unique)
- media: uploaded_by
- users: email (unique)
- settings: key (unique)

---

## Relationships Diagram

```
┌─────────────┐     ┌─────────────┐     ┌─────────────┐
│   Category  │────<│ category_post│>────│    Post     │
└─────────────┘     └─────────────┘     └─────────────┘
       │                                       │
       │ parent_id                   author_id │
       ▼                                       │
┌─────────────┐                               │
│   Category  │                               │
└─────────────┘                               │
                                              │
┌─────────────┐     ┌─────────────┐          │
│     Tag     │────<│   post_tag  │>─────────┘
└─────────────┘     └─────────────┘
                                              │
┌─────────────┐                   featured_   │
│    Media    │<──────────────────image_id────┘
└─────────────┘
       │ uploaded_by
       ▼
┌─────────────┐     ┌─────────────┐     ┌─────────────┐
│    User     │────<│model_has_roles>───│    Role     │
└─────────────┘     └─────────────┘     └─────────────┘
                                              │
                    ┌─────────────┐           │
                    │role_has_    │<──────────┘
                    │permissions  │
                    └─────────────┘
                           │
                           ▼
                    ┌─────────────┐
                    │ Permission  │
                    └─────────────┘
```
