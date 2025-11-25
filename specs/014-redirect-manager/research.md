# Research: Redirect Manager

## Redirect Types

### HTTP Status Codes

| Code | Name | Use Case | SEO Impact |
|------|------|----------|------------|
| 301 | Moved Permanently | Content permanently moved | Passes ~90-99% link equity |
| 302 | Found (Temporary) | Temporary redirect | Doesn't pass link equity |
| 307 | Temporary Redirect | Same as 302, method preserved | Doesn't pass link equity |
| 308 | Permanent Redirect | Same as 301, method preserved | Passes link equity |

**Recommendation**: Support 301 and 302 as primary options.

## Middleware Implementation

### Laravel Middleware Strategy

Redirects must be processed before application routing to intercept old URLs.

```php
// app/Http/Middleware/HandleRedirects.php
class HandleRedirects
{
    public function handle(Request $request, Closure $next)
    {
        $path = '/' . ltrim($request->path(), '/');

        $redirect = $this->findRedirect($path);

        if ($redirect) {
            $redirect->increment('hits');

            $targetUrl = $redirect->target_url;

            // Preserve query string
            if ($request->getQueryString()) {
                $separator = str_contains($targetUrl, '?') ? '&' : '?';
                $targetUrl .= $separator . $request->getQueryString();
            }

            return redirect($targetUrl, $redirect->status_code);
        }

        return $next($request);
    }

    protected function findRedirect(string $path): ?Redirect
    {
        return Cache::remember("redirect:{$path}", 3600, function () use ($path) {
            return Redirect::where('source_url', $path)
                ->where('is_active', true)
                ->first();
        });
    }
}
```

### Middleware Registration

```php
// bootstrap/app.php (Laravel 12)
->withMiddleware(function (Middleware $middleware) {
    $middleware->prepend(HandleRedirects::class);
})
```

## Caching Strategy

### Per-URL Caching

```php
// Cache key per URL for fast lookups
$cacheKey = "redirect:" . md5($path);

Cache::remember($cacheKey, 3600, fn() => Redirect::findByPath($path));
```

### Full Table Caching (Alternative for small sites)

```php
// Cache all redirects as array for in-memory lookup
$redirects = Cache::remember('redirects:all', 3600, function () {
    return Redirect::active()
        ->pluck('target_url', 'source_url')
        ->toArray();
});

$targetUrl = $redirects[$path] ?? null;
```

### Cache Invalidation

```php
// In Redirect model
protected static function booted(): void
{
    static::saved(function (Redirect $redirect) {
        Cache::forget("redirect:{$redirect->source_url}");
        Cache::forget('redirects:all');
    });

    static::deleted(function (Redirect $redirect) {
        Cache::forget("redirect:{$redirect->source_url}");
        Cache::forget('redirects:all');
    });
}
```

## Loop Detection

### Validation Rule

```php
public static function rules(string $exceptId = null): array
{
    return [
        'source_url' => [
            'required',
            'string',
            'starts_with:/',
            Rule::unique('redirects', 'source_url')->ignore($exceptId),
        ],
        'target_url' => [
            'required',
            'string',
            'different:source_url',
            function ($attribute, $value, $fail) {
                if ($this->wouldCreateLoop($value)) {
                    $fail('This would create a redirect loop.');
                }
            },
        ],
    ];
}

protected function wouldCreateLoop(string $targetUrl): bool
{
    $visited = [$this->source_url];
    $current = $targetUrl;

    while ($redirect = Redirect::where('source_url', $current)->first()) {
        if (in_array($redirect->target_url, $visited)) {
            return true;
        }
        $visited[] = $current;
        $current = $redirect->target_url;

        // Prevent infinite loop in check
        if (count($visited) > 10) {
            return true;
        }
    }

    return in_array($current, $visited);
}
```

## Auto-Redirect on Slug Change

### Model Observer

```php
// app/Observers/PostObserver.php
class PostObserver
{
    public function updating(Post $post): void
    {
        if ($post->isDirty('slug') && $post->getOriginal('slug')) {
            $oldSlug = $post->getOriginal('slug');
            $newSlug = $post->slug;

            // Don't create if redirect already exists
            if (!Redirect::where('source_url', "/posts/{$oldSlug}")->exists()) {
                Redirect::create([
                    'source_url' => "/posts/{$oldSlug}",
                    'target_url' => "/posts/{$newSlug}",
                    'status_code' => 301,
                    'is_automatic' => true,
                ]);
            }
        }
    }
}

// Register in EventServiceProvider
Post::observe(PostObserver::class);
```

### Handling Chain Redirects

When slug changes multiple times: A → B → C

**Option 1**: Update all existing redirects pointing to old URL
```php
Redirect::where('target_url', "/posts/{$oldSlug}")
    ->update(['target_url' => "/posts/{$newSlug}"]);
```

**Option 2**: Let chain exist (simpler, slightly slower)

**Recommendation**: Option 1 for cleaner redirect table.

## Admin Interface (Filament)

### RedirectResource

```php
class RedirectResource extends Resource
{
    protected static ?string $model = Redirect::class;

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('source_url')
                ->required()
                ->prefix('/')
                ->placeholder('old-page-url')
                ->unique(ignoreRecord: true),

            TextInput::make('target_url')
                ->required()
                ->placeholder('/new-page-url or https://external.com'),

            Select::make('status_code')
                ->options([
                    301 => '301 - Permanent',
                    302 => '302 - Temporary',
                ])
                ->default(301)
                ->required(),

            Toggle::make('is_active')
                ->default(true),

            Placeholder::make('hits')
                ->content(fn($record) => $record?->hits ?? 0)
                ->visibleOn('edit'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('source_url')->searchable()->sortable(),
                TextColumn::make('target_url')->searchable()->limit(30),
                BadgeColumn::make('status_code')
                    ->colors([
                        'success' => 301,
                        'warning' => 302,
                    ]),
                TextColumn::make('hits')->sortable(),
                IconColumn::make('is_automatic')->boolean(),
                TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
```

## Import/Export

### CSV Format

```csv
source_url,target_url,status_code
/old-page,/new-page,301
/blog/old-post,/blog/new-post,301
/legacy/*,/archive,302
```

### Export Action

```php
ExportAction::make()
    ->exporter(RedirectExporter::class)
```

### Import Action

```php
ImportAction::make()
    ->importer(RedirectImporter::class)
```

## Performance Benchmarks

| Scenario | Target | Strategy |
|----------|--------|----------|
| Redirect lookup | < 5ms | Cached per-URL |
| Cache hit | < 1ms | In-memory cache |
| Admin list load | < 500ms | Pagination |
| Bulk import (1k) | < 10s | Chunked inserts |

## Regex/Wildcard Support (Optional)

For pattern-based redirects:

```php
// Store pattern type
Schema::table('redirects', function (Blueprint $table) {
    $table->enum('match_type', ['exact', 'regex', 'wildcard'])->default('exact');
});

// Match in middleware
if ($redirect->match_type === 'regex') {
    if (preg_match($redirect->source_url, $path, $matches)) {
        // Replace placeholders in target
        $target = preg_replace($redirect->source_url, $redirect->target_url, $path);
    }
} elseif ($redirect->match_type === 'wildcard') {
    $pattern = str_replace('*', '(.*)', $redirect->source_url);
    // ...
}
```

**Recommendation**: Start with exact matching, add patterns later if needed.

## Security Considerations

- Validate target URLs to prevent open redirects to external malicious sites
- Rate limit redirect creation via admin
- Log suspicious redirect patterns
- Restrict redirect management to admin roles
