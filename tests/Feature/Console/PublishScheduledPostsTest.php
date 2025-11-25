<?php

declare(strict_types=1);

use App\Models\Post;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('command publishes scheduled posts that are due', function (): void {
    $user = User::factory()->author()->create();

    // Create a post scheduled for yesterday (should be published)
    $duePost = Post::factory()->for($user, 'author')->create([
        'status' => 'scheduled',
        'published_at' => Carbon::yesterday(),
        'title' => 'Due Post',
    ]);

    // Create a post scheduled for tomorrow (should NOT be published)
    $futurePost = Post::factory()->for($user, 'author')->create([
        'status' => 'scheduled',
        'published_at' => Carbon::tomorrow(),
        'title' => 'Future Post',
    ]);

    $this->artisan('posts:publish-scheduled')
        ->expectsOutput('Published: Due Post')
        ->expectsOutput('Published 1 post(s).')
        ->assertSuccessful();

    expect($duePost->fresh()->status)->toBe('published')
        ->and($futurePost->fresh()->status)->toBe('scheduled');
});

test('command handles no scheduled posts gracefully', function (): void {
    $this->artisan('posts:publish-scheduled')
        ->expectsOutput('No scheduled posts to publish.')
        ->assertSuccessful();
});

test('command does not affect draft or published posts', function (): void {
    $user = User::factory()->author()->create();

    $draftPost = Post::factory()->for($user, 'author')->draft()->create();
    $publishedPost = Post::factory()->for($user, 'author')->published()->create();

    $this->artisan('posts:publish-scheduled')
        ->expectsOutput('No scheduled posts to publish.')
        ->assertSuccessful();

    expect($draftPost->fresh()->status)->toBe('draft')
        ->and($publishedPost->fresh()->status)->toBe('published');
});

test('command publishes multiple scheduled posts', function (): void {
    $user = User::factory()->author()->create();

    Post::factory(3)->for($user, 'author')->create([
        'status' => 'scheduled',
        'published_at' => Carbon::yesterday(),
    ]);

    $this->artisan('posts:publish-scheduled')
        ->expectsOutput('Published 3 post(s).')
        ->assertSuccessful();

    expect(Post::where('status', 'published')->count())->toBe(3);
});
