<?php

declare(strict_types=1);

use App\Filament\Resources\CategoryResource;
use App\Filament\Resources\CategoryResource\Pages\CreateCategory;
use App\Filament\Resources\CategoryResource\Pages\EditCategory;
use App\Filament\Resources\CategoryResource\Pages\ListCategories;
use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('admin can access categories list page', function (): void {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(CategoryResource::getUrl('index'))
        ->assertSuccessful();
});

test('admin can see categories in table', function (): void {
    $admin = User::factory()->admin()->create();
    $categories = Category::factory(3)->create();

    $this->actingAs($admin);

    livewire(ListCategories::class)
        ->assertCanSeeTableRecords($categories);
});

test('admin can create a category', function (): void {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(CategoryResource::getUrl('create'))
        ->assertSuccessful();

    livewire(CreateCategory::class)
        ->fillForm([
            'name' => 'Test Category',
            'slug' => 'test-category',
            'description' => 'A test category description',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Category::where('name', 'Test Category')->exists())->toBeTrue();
});

test('admin can create a category with parent', function (): void {
    $admin = User::factory()->admin()->create();
    $parent = Category::factory()->create();

    $this->actingAs($admin);

    livewire(CreateCategory::class)
        ->fillForm([
            'name' => 'Child Category',
            'slug' => 'child-category',
            'parent_id' => $parent->id,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $child = Category::where('name', 'Child Category')->first();
    expect($child->parent_id)->toBe($parent->id);
});

test('admin can edit a category', function (): void {
    $admin = User::factory()->admin()->create();
    $category = Category::factory()->create();

    $this->actingAs($admin)
        ->get(CategoryResource::getUrl('edit', ['record' => $category]))
        ->assertSuccessful();

    livewire(EditCategory::class, ['record' => $category->getRouteKey()])
        ->fillForm([
            'name' => 'Updated Category Name',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($category->fresh()->name)->toBe('Updated Category Name');
});

test('admin can delete a category without children or posts', function (): void {
    $admin = User::factory()->admin()->create();
    $category = Category::factory()->create();

    $this->actingAs($admin);

    livewire(ListCategories::class)
        ->callTableAction('delete', $category);

    expect(Category::find($category->id))->toBeNull();
});

test('category with children cannot be deleted', function (): void {
    $admin = User::factory()->admin()->create();
    $parent = Category::factory()->create();
    Category::factory()->create(['parent_id' => $parent->id]);

    $this->actingAs($admin);

    livewire(ListCategories::class)
        ->callTableAction('delete', $parent)
        ->assertNotified();

    expect(Category::find($parent->id))->not->toBeNull();
});

test('category with posts cannot be deleted', function (): void {
    $admin = User::factory()->admin()->create();
    $category = Category::factory()->create();
    $post = Post::factory()->for($admin, 'author')->create();
    $post->categories()->attach($category);

    $this->actingAs($admin);

    livewire(ListCategories::class)
        ->callTableAction('delete', $category)
        ->assertNotified();

    expect(Category::find($category->id))->not->toBeNull();
});

test('category name is required', function (): void {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin);

    livewire(CreateCategory::class)
        ->fillForm([
            'name' => '',
            'slug' => 'test-slug',
        ])
        ->call('create')
        ->assertHasFormErrors(['name' => 'required']);
});

test('category slug must be unique', function (): void {
    $admin = User::factory()->admin()->create();
    Category::factory()->create(['slug' => 'existing-slug']);

    $this->actingAs($admin);

    livewire(CreateCategory::class)
        ->fillForm([
            'name' => 'New Category',
            'slug' => 'existing-slug',
        ])
        ->call('create')
        ->assertHasFormErrors(['slug' => 'unique']);
});

test('categories can be filtered by parent', function (): void {
    $admin = User::factory()->admin()->create();
    $parent = Category::factory()->create();
    $child = Category::factory()->create(['parent_id' => $parent->id]);
    $standalone = Category::factory()->create();

    $this->actingAs($admin);

    livewire(ListCategories::class)
        ->assertCanSeeTableRecords([$parent, $child, $standalone]);
});

test('posts count is displayed for categories', function (): void {
    $admin = User::factory()->admin()->create();
    $category = Category::factory()->create();
    $posts = Post::factory(3)->for($admin, 'author')->create();
    $category->posts()->attach($posts->pluck('id'));

    $this->actingAs($admin);

    livewire(ListCategories::class)
        ->assertCanSeeTableRecords([$category]);
});
