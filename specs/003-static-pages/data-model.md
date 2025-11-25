# Data Model: Static Pages

## Entities

### Page

| Field | Type | Constraints | Description |
|-------|------|-------------|-------------|
| id | bigint | PK, auto-increment | Primary key |
| title | string(255) | required | Page title |
| slug | string(255) | required, unique | URL slug |
| content | text | required | Rich text content |
| excerpt | text | nullable | Short description |
| parent_id | bigint | nullable, FK → pages.id | Parent page reference |
| author_id | bigint | required, FK → users.id | Page author |
| status | enum | draft, scheduled, published | Publication status |
| template | string(50) | default: 'default' | Blade template name |
| published_at | datetime | nullable | Publication date |
| featured_image_id | bigint | nullable, FK → media.id | Featured image |
| meta_title | string(60) | nullable | SEO title |
| meta_description | string(160) | nullable | SEO description |
| focus_keyword | string(100) | nullable | SEO focus keyword |
| sort_order | integer | default: 0 | Display order among siblings |
| created_at | datetime | auto | Creation timestamp |
| updated_at | datetime | auto | Last update timestamp |
| deleted_at | datetime | nullable | Soft delete timestamp |

### Relationships

```
Page belongsTo User (author)
Page belongsTo Page (parent) - nullable
Page hasMany Page (children)
Page belongsTo Media (featuredImage) - nullable
```

### Indexes

- `pages_slug_unique` on slug
- `pages_parent_id_index` on parent_id
- `pages_status_published_at_index` on (status, published_at)
- `pages_author_id_index` on author_id

## State Transitions

```
[New] → draft
draft → scheduled (when published_at is future)
draft → published (when published_at is now or past)
scheduled → published (automatic at published_at time)
published → draft (unpublish)
any → deleted (soft delete)
deleted → any (restore)
```

## Validation Rules

| Field | Rules |
|-------|-------|
| title | required, max:255 |
| slug | required, max:255, alpha_dash, unique (excluding self), not_reserved |
| content | required |
| parent_id | nullable, exists:pages,id, not_self, max_depth:3 |
| status | required, in:draft,scheduled,published |
| template | required, in:default,full-width,with-sidebar |
| published_at | required_if:status,scheduled,published |
| meta_title | nullable, max:60 |
| meta_description | nullable, max:160 |

## Reserved Slugs

Cannot be used as page slugs:
- admin, api, login, logout, register, dashboard
- feed, sitemap, robots.txt
- Any existing post slug
