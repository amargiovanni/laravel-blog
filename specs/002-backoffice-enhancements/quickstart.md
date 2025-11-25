# Quickstart Guide: Backoffice Enhancements

**Feature**: 002-backoffice-enhancements
**Date**: 2025-11-25

## Prerequisites

Before starting implementation, ensure:

1. ✅ Laravel 12 application running
2. ✅ Filament 4.x admin panel configured
3. ✅ Spatie Laravel-Permission installed and seeded
4. ✅ Existing models: Post, Category, Tag, Media, User, Setting
5. ✅ Pest v4 test framework configured

## Implementation Order

Follow this sequence for test-first development:

### Phase 1: Categories & Tags (P1)

```bash
# 1. Create tests first
php artisan make:test Feature/Filament/CategoryResourceTest --pest
php artisan make:test Feature/Filament/TagResourceTest --pest

# 2. Create policies
php artisan make:policy CategoryPolicy --model=Category
php artisan make:policy TagPolicy --model=Tag

# 3. Create resources (after tests written)
php artisan make:filament-resource Category --generate
php artisan make:filament-resource Tag --generate

# 4. Run tests
php artisan test --filter=CategoryResource
php artisan test --filter=TagResource
```

### Phase 2: Media Library (P1)

```bash
# 1. Create tests
php artisan make:test Feature/Filament/MediaResourceTest --pest

# 2. Create policy
php artisan make:policy MediaPolicy --model=Media

# 3. Create resource
php artisan make:filament-resource Media --generate

# 4. Enhance existing ImageService if needed

# 5. Run tests
php artisan test --filter=MediaResource
```

### Phase 3: User Management (P1)

```bash
# 1. Install Filament Shield
composer require bezhansalleh/filament-shield "^3.0"
php artisan vendor:publish --tag="filament-shield-config"
php artisan shield:install

# 2. Create migration for is_active
php artisan make:migration add_is_active_to_users_table

# 3. Create tests
php artisan make:test Feature/Filament/UserResourceTest --pest

# 4. Create/modify policy
php artisan make:policy UserPolicy --model=User

# 5. Create resource
php artisan make:filament-resource User --generate

# 6. Generate Shield permissions
php artisan shield:generate --all

# 7. Run tests
php artisan test --filter=UserResource
```

### Phase 4: Theme Settings (P2)

```bash
# 1. Create tests
php artisan make:test Feature/Filament/ThemeSettingsTest --pest

# 2. Create Filament Page
php artisan make:filament-page ThemeSettings

# 3. Update blog layout for CSS variables

# 4. Run tests
php artisan test --filter=ThemeSettings
```

### Phase 5: GEO Features (P2)

```bash
# 1. Create tests
php artisan make:test Feature/GEO/LlmsTxtTest --pest
php artisan make:test Feature/GEO/JsonLdTest --pest
php artisan make:test Unit/Services/LlmsTxtServiceTest --pest
php artisan make:test Unit/Services/JsonLdServiceTest --pest

# 2. Create services
php artisan make:class Services/LlmsTxtService
php artisan make:class Services/JsonLdService

# 3. Add route for llms.txt

# 4. Create Filament Page
php artisan make:filament-page GeoSettings

# 5. Update PostObserver

# 6. Run tests
php artisan test --filter=GEO
php artisan test --filter=LlmsTxt
php artisan test --filter=JsonLd
```

## Key Test Patterns

### Filament Resource Test (Example)

```php
<?php

declare(strict_types=1);

use App\Filament\Resources\CategoryResource;
use App\Filament\Resources\CategoryResource\Pages\CreateCategory;
use App\Filament\Resources\CategoryResource\Pages\EditCategory;
use App\Filament\Resources\CategoryResource\Pages\ListCategories;
use App\Models\Category;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('admin can access categories list', function (): void {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(CategoryResource::getUrl('index'))
        ->assertSuccessful();
});

test('admin can create category', function (): void {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin);

    livewire(CreateCategory::class)
        ->fillForm([
            'name' => 'Test Category',
            'slug' => 'test-category',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Category::where('name', 'Test Category')->exists())->toBeTrue();
});

test('category with children cannot be deleted', function (): void {
    $admin = User::factory()->admin()->create();
    $parent = Category::factory()->create();
    $child = Category::factory()->create(['parent_id' => $parent->id]);

    $this->actingAs($admin);

    livewire(ListCategories::class)
        ->callTableAction('delete', $parent)
        ->assertNotified(); // Should show error notification

    expect(Category::find($parent->id))->not->toBeNull();
});
```

### Service Test (Example)

```php
<?php

declare(strict_types=1);

use App\Models\Post;
use App\Models\User;
use App\Services\LlmsTxtService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('generates valid llms.txt content', function (): void {
    $author = User::factory()->create();
    $posts = Post::factory(3)->for($author, 'author')->published()->create();

    $service = new LlmsTxtService();
    $content = $service->generate();

    expect($content)
        ->toContain('# ')  // Has H1
        ->toContain('## ') // Has sections
        ->toContain($posts->first()->title);
});

test('excludes unpublished posts', function (): void {
    $author = User::factory()->create();
    $published = Post::factory()->for($author, 'author')->published()->create();
    $draft = Post::factory()->for($author, 'author')->draft()->create();

    $service = new LlmsTxtService();
    $content = $service->generate();

    expect($content)
        ->toContain($published->title)
        ->not->toContain($draft->title);
});
```

## Common Commands

```bash
# Run all tests
php artisan test

# Run specific feature tests
php artisan test tests/Feature/Filament/

# Run with coverage
php artisan test --coverage

# Fix code style
vendor/bin/pint --dirty

# Static analysis
./vendor/bin/phpstan analyse

# Build frontend assets
npm run build

# Clear caches after changes
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

## Environment Configuration

Add these to `.env` if not present:

```env
# Media settings
MEDIA_DISK=public
MEDIA_MAX_SIZE=10240
MEDIA_CONVERT_TO_WEBP=true

# Default theme colors
THEME_PRIMARY_COLOR=#3B82F6
THEME_SECONDARY_COLOR=#10B981
```

## Verification Checklist

After completing each phase:

- [ ] All tests pass (`php artisan test`)
- [ ] Pint passes (`vendor/bin/pint --dirty`)
- [ ] Larastan passes (`./vendor/bin/phpstan analyse`)
- [ ] Manual verification in browser
- [ ] Admin panel accessible at `/admin`
- [ ] New resources appear in navigation
- [ ] Permissions properly restrict access

## Troubleshooting

### Shield not generating permissions

```bash
php artisan shield:generate --all --panel=admin
```

### Filament cache issues

```bash
php artisan filament:clear-cached-components
php artisan icons:clear
```

### Media uploads failing

Check storage link:
```bash
php artisan storage:link
```

Verify permissions:
```bash
chmod -R 775 storage/app/public
```
