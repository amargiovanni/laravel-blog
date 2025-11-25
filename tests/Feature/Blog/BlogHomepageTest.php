<?php

declare(strict_types=1);

use App\Models\Post;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('homepage displays published posts', function (): void {
    $user = User::factory()->author()->create();

    $publishedPost = Post::factory()->for($user, 'author')->published()->create([
        'title' => 'My Published Post',
    ]);

    $this->get('/')
        ->assertSuccessful()
        ->assertSee('My Published Post');
});

test('homepage does not display draft posts', function (): void {
    $user = User::factory()->author()->create();

    $draftPost = Post::factory()->for($user, 'author')->draft()->create([
        'title' => 'My Draft Post',
    ]);

    $this->get('/')
        ->assertSuccessful()
        ->assertDontSee('My Draft Post');
});

test('homepage does not display scheduled posts', function (): void {
    $user = User::factory()->author()->create();

    $scheduledPost = Post::factory()->for($user, 'author')->scheduled()->create([
        'title' => 'My Scheduled Post',
    ]);

    $this->get('/')
        ->assertSuccessful()
        ->assertDontSee('My Scheduled Post');
});

test('homepage displays latest posts first', function (): void {
    $user = User::factory()->author()->create();

    $olderPost = Post::factory()->for($user, 'author')->published()->create([
        'title' => 'Older Post',
        'published_at' => now()->subDays(5),
    ]);

    $newerPost = Post::factory()->for($user, 'author')->published()->create([
        'title' => 'Newer Post',
        'published_at' => now()->subDay(),
    ]);

    $response = $this->get('/');
    $response->assertSuccessful();

    // Newer post should appear before older post in the response
    $content = $response->getContent();
    $newerPosition = strpos($content, 'Newer Post');
    $olderPosition = strpos($content, 'Older Post');

    expect($newerPosition)->toBeLessThan($olderPosition);
});

test('homepage shows blog name in title', function (): void {
    $this->get('/')
        ->assertSuccessful()
        ->assertSee(config('blog.name', config('app.name')));
});
