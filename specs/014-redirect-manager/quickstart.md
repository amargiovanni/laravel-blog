# Quickstart: Redirect Manager

## Prerequisites

- Laravel 12 application
- Filament 4.x installed
- Post model from 001-blog-engine

## Installation Steps

### 1. Create Migration

```bash
php artisan make:migration create_redirects_table --no-interaction
```

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('redirects', function (Blueprint $table) {
            $table->id();
            $table->string('source_url', 2048);
            $table->string('target_url', 2048);
            $table->smallInteger('status_code')->default(301);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_automatic')->default(false);
            $table->unsignedInteger('hits')->default(0);
            $table->timestamp('last_hit_at')->nullable();
            $table->timestamps();

            $table->unique('source_url');
            $table->index(['is_active', 'source_url']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('redirects');
    }
};
```

### 2. Create Model

```bash
php artisan make:model Redirect --no-interaction
```

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Redirect extends Model
{
    protected $fillable = [
        'source_url', 'target_url', 'status_code',
        'is_active', 'is_automatic', 'hits', 'last_hit_at',
    ];

    protected $casts = [
        'status_code' => 'integer',
        'is_active' => 'boolean',
        'is_automatic' => 'boolean',
        'hits' => 'integer',
        'last_hit_at' => 'datetime',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    protected static function booted(): void
    {
        static::saved(fn($r) => Cache::forget("redirect:" . md5($r->source_url)));
        static::deleted(fn($r) => Cache::forget("redirect:" . md5($r->source_url)));
    }

    public function recordHit(): void
    {
        $this->increment('hits');
        $this->update(['last_hit_at' => now()]);
    }
}
```

### 3. Create Middleware

```bash
php artisan make:middleware HandleRedirects --no-interaction
```

```php
<?php

namespace App\Http\Middleware;

use App\Models\Redirect;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class HandleRedirects
{
    public function handle(Request $request, Closure $next): Response
    {
        $path = '/' . ltrim($request->path(), '/');

        $redirect = $this->findRedirect($path);

        if ($redirect) {
            // Record hit asynchronously or inline
            $redirect->recordHit();

            $targetUrl = $redirect->target_url;

            // Preserve query string
            if ($queryString = $request->getQueryString()) {
                $separator = str_contains($targetUrl, '?') ? '&' : '?';
                $targetUrl .= $separator . $queryString;
            }

            return redirect($targetUrl, $redirect->status_code);
        }

        return $next($request);
    }

    protected function findRedirect(string $path): ?Redirect
    {
        $cacheKey = "redirect:" . md5($path);

        return Cache::remember($cacheKey, 3600, function () use ($path) {
            return Redirect::where('source_url', $path)
                ->where('is_active', true)
                ->first();
        });
    }
}
```

### 4. Register Middleware

```php
// bootstrap/app.php

->withMiddleware(function (Middleware $middleware) {
    $middleware->prepend(\App\Http\Middleware\HandleRedirects::class);
})
```

### 5. Create Post Observer (Auto-Redirect)

```bash
php artisan make:observer PostObserver --model=Post --no-interaction
```

```php
<?php

namespace App\Observers;

use App\Models\Post;
use App\Models\Redirect;

class PostObserver
{
    public function updating(Post $post): void
    {
        if ($post->isDirty('slug') && $post->getOriginal('slug')) {
            $oldSlug = $post->getOriginal('slug');
            $newSlug = $post->slug;

            // Create redirect from old to new
            Redirect::firstOrCreate(
                ['source_url' => "/posts/{$oldSlug}"],
                [
                    'target_url' => "/posts/{$newSlug}",
                    'status_code' => 301,
                    'is_automatic' => true,
                ]
            );

            // Update any existing redirects pointing to old URL
            Redirect::where('target_url', "/posts/{$oldSlug}")
                ->update(['target_url' => "/posts/{$newSlug}"]);
        }
    }
}
```

### 6. Register Observer

```php
// app/Providers/AppServiceProvider.php

use App\Models\Post;
use App\Observers\PostObserver;

public function boot(): void
{
    Post::observe(PostObserver::class);
}
```

### 7. Create Filament Resource

```bash
php artisan make:filament-resource Redirect --no-interaction
```

```php
<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RedirectResource\Pages;
use App\Models\Redirect;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class RedirectResource extends Resource
{
    protected static ?string $model = Redirect::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-right-circle';

    protected static ?string $navigationGroup = 'SEO';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('source_url')
                ->required()
                ->prefix('/')
                ->placeholder('old-page-url')
                ->maxLength(2048),

            Forms\Components\TextInput::make('target_url')
                ->required()
                ->placeholder('/new-page-url')
                ->maxLength(2048),

            Forms\Components\Select::make('status_code')
                ->options([
                    301 => '301 - Permanent',
                    302 => '302 - Temporary',
                ])
                ->default(301)
                ->required(),

            Forms\Components\Toggle::make('is_active')
                ->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('source_url')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('target_url')
                    ->searchable()
                    ->limit(40),

                Tables\Columns\BadgeColumn::make('status_code')
                    ->colors([
                        'success' => 301,
                        'warning' => 302,
                    ]),

                Tables\Columns\TextColumn::make('hits')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_automatic')
                    ->boolean()
                    ->label('Auto'),

                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status_code')
                    ->options([
                        301 => '301 - Permanent',
                        302 => '302 - Temporary',
                    ]),
                Tables\Filters\TernaryFilter::make('is_automatic'),
                Tables\Filters\TernaryFilter::make('is_active'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRedirects::route('/'),
            'create' => Pages\CreateRedirect::route('/create'),
            'edit' => Pages\EditRedirect::route('/{record}/edit'),
        ];
    }
}
```

### 8. Run Migrations

```bash
php artisan migrate
```

## Verification

```bash
# Create a test redirect via tinker
php artisan tinker --execute="
App\Models\Redirect::create([
    'source_url' => '/test-old',
    'target_url' => '/test-new',
    'status_code' => 301,
]);
"

# Test the redirect
curl -I http://localhost/test-old
# Should show: HTTP/1.1 301 Moved Permanently
# Location: http://localhost/test-new
```

## Testing Auto-Redirect

```bash
# In tinker
$post = App\Models\Post::first();
$oldSlug = $post->slug;
$post->update(['slug' => 'new-test-slug']);

# Check redirect was created
App\Models\Redirect::where('source_url', "/posts/{$oldSlug}")->first();
```

## Next Steps

1. Add loop detection validation
2. Add content conflict warnings
3. Implement CSV import/export
4. Add usage analytics dashboard widget
