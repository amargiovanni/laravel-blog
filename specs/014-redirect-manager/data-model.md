# Data Model: Redirect Manager

## Database Schema

### redirects

```
┌─────────────────────────────────────────────────────────┐
│                     redirects                           │
├─────────────────────────────────────────────────────────┤
│ id                │ bigint PK auto_increment            │
│ source_url        │ string(2048) unique                 │
│ target_url        │ string(2048)                        │
│ status_code       │ smallint (301 or 302)               │
│ is_active         │ boolean default true                │
│ is_automatic      │ boolean default false               │
│ hits              │ int unsigned default 0              │
│ last_hit_at       │ timestamp nullable                  │
│ created_at        │ timestamp                           │
│ updated_at        │ timestamp                           │
└─────────────────────────────────────────────────────────┘

Indexes:
- PRIMARY (id)
- UNIQUE (source_url)
- INDEX (is_active)
- INDEX (status_code)
- INDEX (created_at)
```

## Migration

```php
Schema::create('redirects', function (Blueprint $table) {
    $table->id();
    $table->string('source_url', 2048)->unique();
    $table->string('target_url', 2048);
    $table->smallInteger('status_code')->default(301);
    $table->boolean('is_active')->default(true);
    $table->boolean('is_automatic')->default(false);
    $table->unsignedInteger('hits')->default(0);
    $table->timestamp('last_hit_at')->nullable();
    $table->timestamps();

    $table->index('is_active');
    $table->index(['source_url', 'is_active']);
});
```

## Model Definition

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Redirect extends Model
{
    protected $fillable = [
        'source_url',
        'target_url',
        'status_code',
        'is_active',
        'is_automatic',
        'hits',
        'last_hit_at',
    ];

    protected $casts = [
        'status_code' => 'integer',
        'is_active' => 'boolean',
        'is_automatic' => 'boolean',
        'hits' => 'integer',
        'last_hit_at' => 'datetime',
    ];

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeAutomatic($query)
    {
        return $query->where('is_automatic', true);
    }

    public function scopeManual($query)
    {
        return $query->where('is_automatic', false);
    }

    // Boot
    protected static function booted(): void
    {
        static::saved(function (Redirect $redirect) {
            Cache::forget("redirect:" . md5($redirect->source_url));
            Cache::forget('redirects:all');
        });

        static::deleted(function (Redirect $redirect) {
            Cache::forget("redirect:" . md5($redirect->source_url));
            Cache::forget('redirects:all');
        });
    }

    // Methods
    public function recordHit(): void
    {
        $this->increment('hits');
        $this->update(['last_hit_at' => now()]);
    }

    // Validation
    public static function rules(?int $exceptId = null): array
    {
        return [
            'source_url' => [
                'required',
                'string',
                'max:2048',
                'starts_with:/',
                Rule::unique('redirects', 'source_url')->ignore($exceptId),
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
        ];
    }
}
```

## URL Format Rules

### Source URL

| Rule | Example | Valid |
|------|---------|-------|
| Must start with `/` | `/old-page` | ✅ |
| No domain | `https://example.com/old` | ❌ |
| Can have path segments | `/blog/2024/old-post` | ✅ |
| Can have extension | `/document.pdf` | ✅ |
| No query strings stored | `/page?id=1` | Store as `/page` |

### Target URL

| Rule | Example | Valid |
|------|---------|-------|
| Internal path | `/new-page` | ✅ |
| Full external URL | `https://other.com/page` | ✅ |
| Different from source | Same as source | ❌ |

## Status Codes

| Code | Constant | Description | SEO Impact |
|------|----------|-------------|------------|
| 301 | `MOVED_PERMANENTLY` | Content permanently moved | Passes link equity |
| 302 | `FOUND` | Temporary redirect | Does not pass link equity |

## Validation Rules

### Loop Detection

```php
// Check if creating this redirect would cause a loop
public function wouldCreateLoop(string $targetUrl): bool
{
    $visited = [$this->source_url];
    $current = $targetUrl;
    $maxDepth = 10;

    for ($i = 0; $i < $maxDepth; $i++) {
        // Check if target loops back to any visited URL
        if (in_array($current, $visited)) {
            return true;
        }

        // Find redirect from current target
        $nextRedirect = static::where('source_url', $current)->first();

        if (!$nextRedirect) {
            break;
        }

        $visited[] = $current;
        $current = $nextRedirect->target_url;
    }

    return false;
}
```

### Content Conflict Detection

```php
// Check if source URL matches existing content
public function conflictsWithContent(): bool
{
    $path = ltrim($this->source_url, '/');

    // Check posts
    if (Post::where('slug', $path)->exists()) {
        return true;
    }

    // Check pages
    if (Page::where('slug', $path)->exists()) {
        return true;
    }

    return false;
}
```

## Cache Structure

### Per-URL Cache

```
Key: redirect:{md5(source_url)}
Value: Redirect model or null
TTL: 3600 seconds (1 hour)
```

### All Redirects Cache (for small sites)

```
Key: redirects:all
Value: Array [source_url => ['target' => target_url, 'code' => status_code]]
TTL: 3600 seconds (1 hour)
```

## Import/Export Format

### CSV Structure

```csv
source_url,target_url,status_code,is_active
/old-page,/new-page,301,1
/blog/2023/old-post,/blog/2024/new-post,301,1
/temporary-offer,/new-offer,302,1
```

### Import Validation

| Check | Action on Failure |
|-------|-------------------|
| Source URL format | Skip row, log error |
| Duplicate source URL | Skip row, log warning |
| Would create loop | Skip row, log error |
| Invalid status code | Default to 301 |

## Statistics

### Dashboard Metrics

```php
// Total active redirects
Redirect::active()->count();

// Total hits today
Redirect::whereDate('last_hit_at', today())->sum('hits');

// Top 10 most used redirects
Redirect::orderByDesc('hits')->limit(10)->get();

// Unused redirects (0 hits in last 30 days)
Redirect::where(function ($query) {
    $query->whereNull('last_hit_at')
          ->orWhere('last_hit_at', '<', now()->subDays(30));
})->get();
```

## Relationships

### Related Models

```
┌─────────────┐          ┌─────────────┐
│    Post     │──────────│  Redirect   │
│  (source)   │ Observer │ (automatic) │
└─────────────┘          └─────────────┘

┌─────────────┐          ┌─────────────┐
│    Page     │──────────│  Redirect   │
│  (source)   │ Observer │ (automatic) │
└─────────────┘          └─────────────┘
```

When Post or Page slug changes → Observer creates Redirect with `is_automatic = true`.
