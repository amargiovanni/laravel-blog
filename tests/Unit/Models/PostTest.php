<?php

declare(strict_types=1);

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('post belongs to author', function (): void {
    $user = User::factory()->author()->create();
    $post = Post::factory()->for($user, 'author')->create();

    expect($post->author)->toBeInstanceOf(User::class)
        ->and($post->author->id)->toBe($user->id);
});

test('post can have categories', function (): void {
    $user = User::factory()->author()->create();
    $post = Post::factory()->for($user, 'author')->create();
    $categories = Category::factory(3)->create();

    $post->categories()->attach($categories);

    expect($post->categories)->toHaveCount(3);
});

test('post can have tags', function (): void {
    $user = User::factory()->author()->create();
    $post = Post::factory()->for($user, 'author')->create();
    $tags = Tag::factory(5)->create();

    $post->tags()->attach($tags);

    expect($post->tags)->toHaveCount(5);
});

test('post has published scope', function (): void {
    $user = User::factory()->author()->create();

    Post::factory()->for($user, 'author')->draft()->create();
    Post::factory()->for($user, 'author')->scheduled()->create();
    Post::factory()->for($user, 'author')->published()->create();
    Post::factory()->for($user, 'author')->published()->create();

    expect(Post::published()->count())->toBe(2);
});

test('post has draft scope', function (): void {
    $user = User::factory()->author()->create();

    Post::factory()->for($user, 'author')->draft()->create();
    Post::factory()->for($user, 'author')->draft()->create();
    Post::factory()->for($user, 'author')->published()->create();

    expect(Post::draft()->count())->toBe(2);
});

test('post generates unique slug', function (): void {
    $user = User::factory()->author()->create();

    $post1 = Post::factory()->for($user, 'author')->create(['title' => 'My First Post']);
    $post2 = Post::factory()->for($user, 'author')->create(['title' => 'My First Post']);

    expect($post1->slug)->toBe('my-first-post')
        ->and($post2->slug)->not->toBe($post1->slug);
});

test('post is soft deleted', function (): void {
    $user = User::factory()->author()->create();
    $post = Post::factory()->for($user, 'author')->create();

    $post->delete();

    expect(Post::count())->toBe(0)
        ->and(Post::withTrashed()->count())->toBe(1);
});
