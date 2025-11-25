<?php

declare(strict_types=1);

use App\Filament\Resources\TagResource;
use App\Filament\Resources\TagResource\Pages\CreateTag;
use App\Filament\Resources\TagResource\Pages\EditTag;
use App\Filament\Resources\TagResource\Pages\ListTags;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('admin can access tags list page', function (): void {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(TagResource::getUrl('index'))
        ->assertSuccessful();
});

test('admin can see tags in table', function (): void {
    $admin = User::factory()->admin()->create();
    $tags = Tag::factory(3)->create();

    $this->actingAs($admin);

    livewire(ListTags::class)
        ->assertCanSeeTableRecords($tags);
});

test('admin can create a tag', function (): void {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(TagResource::getUrl('create'))
        ->assertSuccessful();

    livewire(CreateTag::class)
        ->fillForm([
            'name' => 'Test Tag',
            'slug' => 'test-tag',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Tag::where('name', 'Test Tag')->exists())->toBeTrue();
});

test('admin can edit a tag', function (): void {
    $admin = User::factory()->admin()->create();
    $tag = Tag::factory()->create();

    $this->actingAs($admin)
        ->get(TagResource::getUrl('edit', ['record' => $tag]))
        ->assertSuccessful();

    livewire(EditTag::class, ['record' => $tag->getRouteKey()])
        ->fillForm([
            'name' => 'Updated Tag Name',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($tag->fresh()->name)->toBe('Updated Tag Name');
});

test('admin can delete a tag', function (): void {
    $admin = User::factory()->admin()->create();
    $tag = Tag::factory()->create();

    $this->actingAs($admin);

    livewire(ListTags::class)
        ->callTableAction('delete', $tag);

    expect(Tag::find($tag->id))->toBeNull();
});

test('tag name is required', function (): void {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin);

    livewire(CreateTag::class)
        ->fillForm([
            'name' => '',
            'slug' => 'test-slug',
        ])
        ->call('create')
        ->assertHasFormErrors(['name' => 'required']);
});

test('tag slug must be unique', function (): void {
    $admin = User::factory()->admin()->create();
    Tag::factory()->create(['slug' => 'existing-slug']);

    $this->actingAs($admin);

    livewire(CreateTag::class)
        ->fillForm([
            'name' => 'New Tag',
            'slug' => 'existing-slug',
        ])
        ->call('create')
        ->assertHasFormErrors(['slug' => 'unique']);
});

test('tags can be searched by name', function (): void {
    $admin = User::factory()->admin()->create();
    $phpTag = Tag::factory()->create(['name' => 'PHP']);
    $laravelTag = Tag::factory()->create(['name' => 'Laravel']);
    $jsTag = Tag::factory()->create(['name' => 'JavaScript']);

    $this->actingAs($admin);

    livewire(ListTags::class)
        ->searchTable('Laravel')
        ->assertCanSeeTableRecords([$laravelTag])
        ->assertCanNotSeeTableRecords([$phpTag, $jsTag]);
});

test('posts count is displayed for tags', function (): void {
    $admin = User::factory()->admin()->create();
    $tag = Tag::factory()->create();
    $posts = Post::factory(3)->for($admin, 'author')->create();
    $tag->posts()->attach($posts->pluck('id'));

    $this->actingAs($admin);

    livewire(ListTags::class)
        ->assertCanSeeTableRecords([$tag]);
});

test('admin can merge tags', function (): void {
    $admin = User::factory()->admin()->create();
    $sourceTag = Tag::factory()->create(['name' => 'Source Tag']);
    $targetTag = Tag::factory()->create(['name' => 'Target Tag']);

    $post = Post::factory()->for($admin, 'author')->create();
    $post->tags()->attach($sourceTag);

    $this->actingAs($admin);

    livewire(ListTags::class)
        ->callTableBulkAction('merge', [$sourceTag], [
            'target_tag_id' => $targetTag->id,
        ]);

    // Source tag should be deleted, post should now have target tag
    expect(Tag::find($sourceTag->id))->toBeNull();
    expect($post->fresh()->tags->pluck('id')->contains($targetTag->id))->toBeTrue();
});

test('admin can bulk delete tags', function (): void {
    $admin = User::factory()->admin()->create();
    $tags = Tag::factory(3)->create();

    $this->actingAs($admin);

    livewire(ListTags::class)
        ->callTableBulkAction('delete', $tags);

    foreach ($tags as $tag) {
        expect(Tag::find($tag->id))->toBeNull();
    }
});
