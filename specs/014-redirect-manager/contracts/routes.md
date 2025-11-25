# Contracts: Redirect Manager

## Middleware Contract

### HandleRedirects Middleware

**Location**: `app/Http/Middleware/HandleRedirects.php`

**Purpose**: Intercept requests and execute redirects before application routing

**Registration**: Must be prepended to middleware stack to run first

```php
// bootstrap/app.php
->withMiddleware(function (Middleware $middleware) {
    $middleware->prepend(\App\Http\Middleware\HandleRedirects::class);
})
```

**Behavior**:

1. Extract path from incoming request
2. Normalize path (ensure leading `/`)
3. Look up redirect in cache or database
4. If found and active:
   - Record hit (increment counter)
   - Preserve query string from original request
   - Return redirect response with appropriate status code
5. If not found: pass to next middleware

**Response Headers**:

```
HTTP/1.1 301 Moved Permanently
Location: /new-url
Cache-Control: no-cache

HTTP/1.1 302 Found
Location: /temporary-url
Cache-Control: no-cache
```

---

## Admin Routes (Filament)

### Redirect Management

| Route | Method | Description |
|-------|--------|-------------|
| /admin/redirects | GET | List all redirects |
| /admin/redirects/create | GET | Create form |
| /admin/redirects | POST | Store new redirect |
| /admin/redirects/{id}/edit | GET | Edit form |
| /admin/redirects/{id} | PUT | Update redirect |
| /admin/redirects/{id} | DELETE | Delete redirect |

### Bulk Actions

| Route | Method | Description |
|-------|--------|-------------|
| /admin/redirects/bulk-delete | POST | Delete selected redirects |
| /admin/redirects/export | GET | Export to CSV |
| /admin/redirects/import | POST | Import from CSV |

---

## Request/Response Flow

```
┌──────────────┐     ┌────────────────────┐     ┌─────────────┐
│   Browser    │────▶│ HandleRedirects    │────▶│  App Router │
│              │     │    Middleware      │     │             │
└──────────────┘     └────────────────────┘     └─────────────┘
       ▲                      │
       │                      │ (redirect found)
       │                      ▼
       │              ┌────────────────────┐
       └──────────────│  301/302 Response  │
                      │  Location: /new    │
                      └────────────────────┘
```

---

## Model Observer Contract

### PostObserver

**Location**: `app/Observers/PostObserver.php`

**Events Handled**:
- `updating`: Detect slug changes before save

**Behavior**:

```php
public function updating(Post $post): void
{
    // Only if slug is changing and old slug exists
    if ($post->isDirty('slug') && $post->getOriginal('slug')) {
        // Create redirect from old URL to new URL
        // Update existing redirects pointing to old URL
    }
}
```

**Redirect Created**:

| Field | Value |
|-------|-------|
| source_url | `/posts/{old_slug}` |
| target_url | `/posts/{new_slug}` |
| status_code | 301 |
| is_automatic | true |

---

## Validation Contract

### Create/Update Redirect

**Form Request**: `StoreRedirectRequest` / `UpdateRedirectRequest`

**Rules**:

```php
[
    'source_url' => [
        'required',
        'string',
        'max:2048',
        'starts_with:/',
        'unique:redirects,source_url,' . $this->route('redirect'),
        new NotSelfRedirect(),
        new NoRedirectLoop(),
    ],
    'target_url' => [
        'required',
        'string',
        'max:2048',
        'different:source_url',
    ],
    'status_code' => [
        'required',
        'in:301,302',
    ],
    'is_active' => [
        'boolean',
    ],
]
```

**Custom Validation Rules**:

| Rule | Purpose |
|------|---------|
| `NotSelfRedirect` | Prevents source = target |
| `NoRedirectLoop` | Detects circular redirect chains |
| `NoContentConflict` | Warns if URL matches existing content (soft) |

---

## Cache Contract

### Cache Keys

| Key Pattern | Value | TTL |
|-------------|-------|-----|
| `redirect:{md5(source_url)}` | Redirect model or null | 3600s |
| `redirects:all` | Array of all active redirects | 3600s |

### Cache Invalidation

Triggered by model events:
- `Redirect::saved` → Clear specific URL cache
- `Redirect::deleted` → Clear specific URL cache

---

## Import/Export Contract

### CSV Export Format

```csv
id,source_url,target_url,status_code,is_active,is_automatic,hits,created_at
1,/old-page,/new-page,301,1,0,150,2025-01-15 10:30:00
2,/blog/old-post,/blog/new-post,301,1,1,45,2025-01-14 08:00:00
```

### CSV Import Format

```csv
source_url,target_url,status_code
/old-page,/new-page,301
/blog/old-post,/blog/new-post,302
```

**Import Behavior**:

| Scenario | Action |
|----------|--------|
| Valid row | Create redirect |
| Duplicate source_url | Skip, log warning |
| Invalid format | Skip, log error |
| Would create loop | Skip, log error |

---

## Query String Handling

Query strings are preserved during redirect:

| Original Request | Redirect Target | Final URL |
|------------------|-----------------|-----------|
| `/old?page=2` | `/new` | `/new?page=2` |
| `/old?page=2` | `/new?sort=date` | `/new?sort=date&page=2` |
| `/old` | `/new?ref=redirect` | `/new?ref=redirect` |

---

## Performance Requirements

| Metric | Target | Strategy |
|--------|--------|----------|
| Redirect lookup | < 5ms | Per-URL caching |
| Cache hit | < 1ms | Memory cache |
| Hit recording | Async or < 2ms | Fire and forget |
| Admin list load | < 500ms | Pagination |

---

## Permissions

| Action | Required Permission |
|--------|---------------------|
| View redirects | `view_redirects` |
| Create redirect | `create_redirects` |
| Edit redirect | `edit_redirects` |
| Delete redirect | `delete_redirects` |
| Import/Export | `manage_redirects` |

Typically restricted to `admin` and `super_admin` roles.
