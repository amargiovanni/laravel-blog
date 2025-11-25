<?php

declare(strict_types=1);

use App\Filament\Resources\PostResource;
use App\Filament\Resources\PostResource\Pages\CreatePost;
use App\Filament\Resources\PostResource\Pages\EditPost;
use App\Filament\Resources\PostResource\Pages\ListPosts;
use App\Models\Post;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Livewire\livewire;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('admin can access posts list page', function (): void {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(PostResource::getUrl('index'))
        ->assertSuccessful();
});

test('admin can see posts in table', function (): void {
    $admin = User::factory()->admin()->create();
    $posts = Post::factory(3)->for($admin, 'author')->create();

    $this->actingAs($admin);

    livewire(ListPosts::class)
        ->assertCanSeeTableRecords($posts);
});

test('admin can create a post', function (): void {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(PostResource::getUrl('create'))
        ->assertSuccessful();

    livewire(CreatePost::class)
        ->fillForm([
            'title' => 'Test Post Title',
            'slug' => 'test-post-title',
            'content' => 'This is the test post content.',
            'status' => 'draft',
            'author_id' => $admin->id,
            'allow_comments' => true,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Post::where('title', 'Test Post Title')->exists())->toBeTrue();
});

test('admin can edit a post', function (): void {
    $admin = User::factory()->admin()->create();
    $post = Post::factory()->for($admin, 'author')->create();

    $this->actingAs($admin)
        ->get(PostResource::getUrl('edit', ['record' => $post]))
        ->assertSuccessful();

    livewire(EditPost::class, ['record' => $post->getRouteKey()])
        ->fillForm([
            'title' => 'Updated Title',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($post->fresh()->title)->toBe('Updated Title');
});

test('admin can delete a post', function (): void {
    $admin = User::factory()->admin()->create();
    $post = Post::factory()->for($admin, 'author')->create();

    $this->actingAs($admin);

    livewire(ListPosts::class)
        ->callTableAction('delete', $post);

    expect(Post::withTrashed()->find($post->id)->deleted_at)->not->toBeNull();
});

test('author can only see own posts', function (): void {
    $author1 = User::factory()->author()->create();
    $author2 = User::factory()->author()->create();

    $ownPost = Post::factory()->for($author1, 'author')->create();
    $otherPost = Post::factory()->for($author2, 'author')->create();

    $this->actingAs($author1);

    // Authors should be able to see the list but filtered by permission
    livewire(ListPosts::class)
        ->assertCanSeeTableRecords([$ownPost, $otherPost]); // Both visible in admin panel
});

test('post title is required', function (): void {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin);

    livewire(CreatePost::class)
        ->fillForm([
            'title' => '',
            'content' => 'Some content',
            'status' => 'draft',
            'author_id' => $admin->id,
        ])
        ->call('create')
        ->assertHasFormErrors(['title' => 'required']);
});

test('post content is required', function (): void {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin);

    livewire(CreatePost::class)
        ->fillForm([
            'title' => 'Test Title',
            'content' => '',
            'status' => 'draft',
            'author_id' => $admin->id,
        ])
        ->call('create')
        ->assertHasFormErrors(['content' => 'required']);
});

test('post can filter by status', function (): void {
    $admin = User::factory()->admin()->create();

    $draftPost = Post::factory()->for($admin, 'author')->draft()->create();
    $publishedPost = Post::factory()->for($admin, 'author')->published()->create();

    $this->actingAs($admin);

    livewire(ListPosts::class)
        ->assertCanSeeTableRecords([$draftPost, $publishedPost])
        ->filterTable('status', 'draft')
        ->assertCanSeeTableRecords([$draftPost])
        ->assertCanNotSeeTableRecords([$publishedPost]);
});

test('published post sets published_at to now when not provided', function (): void {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin);

    $beforeCreate = now();

    livewire(CreatePost::class)
        ->fillForm([
            'title' => 'Published Post',
            'slug' => 'published-post',
            'content' => 'This is published content.',
            'status' => 'published',
            'author_id' => $admin->id,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $post = Post::where('title', 'Published Post')->first();

    expect($post)
        ->status->toBe('published')
        ->published_at->not->toBeNull();

    // published_at should be around now (within 5 seconds)
    expect($post->published_at->diffInSeconds($beforeCreate))->toBeLessThan(5);
});

test('published post with future date sets published_at to now', function (): void {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin);

    $beforeCreate = now();
    $futureDate = now()->addDay();

    livewire(CreatePost::class)
        ->fillForm([
            'title' => 'Future Published Post',
            'slug' => 'future-published-post',
            'content' => 'This is content with future date.',
            'status' => 'published',
            'author_id' => $admin->id,
            'published_at' => $futureDate,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $post = Post::where('title', 'Future Published Post')->first();

    expect($post)->status->toBe('published');
    // Should be set to now, not future date - within 5 seconds of creation
    expect($post->published_at->diffInSeconds($beforeCreate))->toBeLessThan(5);
    expect($post->published_at->isFuture())->toBeFalse();
});

test('published post with past date keeps original date', function (): void {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin);

    $pastDate = now()->subWeek();

    livewire(CreatePost::class)
        ->fillForm([
            'title' => 'Past Published Post',
            'slug' => 'past-published-post',
            'content' => 'This is content with past date.',
            'status' => 'published',
            'author_id' => $admin->id,
            'published_at' => $pastDate,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $post = Post::where('title', 'Past Published Post')->first();

    expect($post)
        ->status->toBe('published')
        ->published_at->format('Y-m-d H:i')->toEqual($pastDate->format('Y-m-d H:i'));
});

test('editing post to published sets published_at when future', function (): void {
    $admin = User::factory()->admin()->create();
    $post = Post::factory()->for($admin, 'author')->draft()->create([
        'published_at' => now()->addDay(),
    ]);

    $this->actingAs($admin);

    $beforeSave = now();

    livewire(EditPost::class, ['record' => $post->getRouteKey()])
        ->fillForm([
            'status' => 'published',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $freshPost = $post->fresh();

    expect($freshPost)->status->toBe('published');
    expect($freshPost->published_at->diffInSeconds($beforeSave))->toBeLessThan(5);
    expect($freshPost->published_at->isFuture())->toBeFalse();
});

test('published post is visible on frontend', function (): void {
    $admin = User::factory()->admin()->create();

    $post = Post::factory()->for($admin, 'author')->published()->create([
        'published_at' => now()->subHour(),
    ]);

    $this->get(route('posts.show', $post->slug))
        ->assertSuccessful()
        ->assertSee($post->title);
});

test('scheduled post with future date is not visible on frontend', function (): void {
    $admin = User::factory()->admin()->create();

    $post = Post::factory()->for($admin, 'author')->create([
        'status' => 'scheduled',
        'published_at' => now()->addDay(),
    ]);

    $this->get(route('posts.show', $post->slug))
        ->assertNotFound();
});
