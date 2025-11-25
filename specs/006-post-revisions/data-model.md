# Data Model: Post Revisions

## Entities

### Revision

| Field | Type | Constraints | Description |
|-------|------|-------------|-------------|
| id | bigint | PK | Primary key |
| revisionable_type | string | required | Model class (Post, Page) |
| revisionable_id | bigint | required | Model ID |
| user_id | bigint | FK → users.id | Author of revision |
| revision_number | integer | required | Sequential number |
| title | string(255) | required | Snapshot of title |
| content | text | required | Snapshot of content |
| excerpt | text | nullable | Snapshot of excerpt |
| metadata | json | nullable | Categories, tags, SEO |
| is_autosave | boolean | default: false | Auto vs manual save |
| is_protected | boolean | default: false | Prevent auto-delete |
| created_at | datetime | auto | Revision timestamp |

### Indexes
- `revisions_revisionable_index` on (revisionable_type, revisionable_id)
- `revisions_user_id_index` on user_id
