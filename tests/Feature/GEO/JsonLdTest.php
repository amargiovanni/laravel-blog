<?php

declare(strict_types=1);

use App\Models\Category;
use App\Models\Media;
use App\Models\Post;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('post page includes JSON-LD script tag', function (): void {
    $author = User::factory()->create();
    $post = Post::factory()->create([
        'title' => 'Test Post With JSON-LD',
        'status' => 'published',
        'published_at' => now()->subDay(),
        'author_id' => $author->id,
    ]);

    $response = $this->get("/posts/{$post->slug}");

    $response->assertOk();
    $response->assertSee('<script type="application/ld+json">', false);
});

test('JSON-LD contains BlogPosting type', function (): void {
    $author = User::factory()->create();
    $post = Post::factory()->create([
        'status' => 'published',
        'published_at' => now()->subDay(),
        'author_id' => $author->id,
    ]);

    $response = $this->get("/posts/{$post->slug}");

    $response->assertOk();
    $response->assertSee('"@type":"BlogPosting"', false);
});

test('JSON-LD includes post headline', function (): void {
    $author = User::factory()->create();
    $post = Post::factory()->create([
        'title' => 'My Amazing Post Title',
        'status' => 'published',
        'published_at' => now()->subDay(),
        'author_id' => $author->id,
    ]);

    $response = $this->get("/posts/{$post->slug}");

    $response->assertOk();
    $response->assertSee('"headline":"My Amazing Post Title"', false);
});

test('JSON-LD includes author information', function (): void {
    $author = User::factory()->create(['name' => 'Jane Author']);
    $post = Post::factory()->create([
        'status' => 'published',
        'published_at' => now()->subDay(),
        'author_id' => $author->id,
    ]);

    $response = $this->get("/posts/{$post->slug}");

    $response->assertOk();
    $response->assertSee('"@type":"Person"', false);
    $response->assertSee('"name":"Jane Author"', false);
});

test('JSON-LD includes publisher information', function (): void {
    config(['blog.name' => 'My Test Blog']);

    $author = User::factory()->create();
    $post = Post::factory()->create([
        'status' => 'published',
        'published_at' => now()->subDay(),
        'author_id' => $author->id,
    ]);

    $response = $this->get("/posts/{$post->slug}");

    $response->assertOk();
    $response->assertSee('"@type":"Organization"', false);
    $response->assertSee('"name":"My Test Blog"', false);
});

test('JSON-LD includes datePublished', function (): void {
    $author = User::factory()->create();
    $publishDate = now()->subDays(3);
    $post = Post::factory()->create([
        'status' => 'published',
        'published_at' => $publishDate,
        'author_id' => $author->id,
    ]);

    $response = $this->get("/posts/{$post->slug}");

    $response->assertOk();
    $response->assertSee('"datePublished":"', false);
});

test('JSON-LD includes featured image when available', function (): void {
    $author = User::factory()->create();
    $media = Media::factory()->create([
        'path' => 'media/test-image.jpg',
        'mime_type' => 'image/jpeg',
    ]);
    $post = Post::factory()->create([
        'status' => 'published',
        'published_at' => now()->subDay(),
        'author_id' => $author->id,
        'featured_image_id' => $media->id,
    ]);

    $response = $this->get("/posts/{$post->slug}");

    $response->assertOk();
    $response->assertSee('"image":', false);
});

test('JSON-LD is not included when disabled', function (): void {
    Setting::set('geo.jsonld_enabled', false, 'geo');

    $author = User::factory()->create();
    $post = Post::factory()->create([
        'status' => 'published',
        'published_at' => now()->subDay(),
        'author_id' => $author->id,
    ]);

    $response = $this->get("/posts/{$post->slug}");

    $response->assertOk();
    $response->assertDontSee('<script type="application/ld+json">', false);
});

test('homepage includes WebSite JSON-LD', function (): void {
    $response = $this->get('/');

    $response->assertOk();
    $response->assertSee('"@type":"WebSite"', false);
});

test('homepage includes search action in JSON-LD', function (): void {
    $response = $this->get('/');

    $response->assertOk();
    $response->assertSee('"@type":"SearchAction"', false);
});

test('JSON-LD includes articleSection from category', function (): void {
    $author = User::factory()->create();
    $category = Category::factory()->create(['name' => 'Programming']);
    $post = Post::factory()->create([
        'status' => 'published',
        'published_at' => now()->subDay(),
        'author_id' => $author->id,
    ]);
    $post->categories()->attach($category);

    $response = $this->get("/posts/{$post->slug}");

    $response->assertOk();
    $response->assertSee('"articleSection":"Programming"', false);
});

test('JSON-LD escapes special characters in title', function (): void {
    $author = User::factory()->create();
    $post = Post::factory()->create([
        'title' => 'Test "Quoted" & <Tagged> Post',
        'status' => 'published',
        'published_at' => now()->subDay(),
        'author_id' => $author->id,
    ]);

    $response = $this->get("/posts/{$post->slug}");

    $response->assertOk();
    // JSON encoding should escape these characters
    $response->assertSee('Test \\', false);
});

test('JSON-LD includes mainEntityOfPage', function (): void {
    $author = User::factory()->create();
    $post = Post::factory()->create([
        'status' => 'published',
        'published_at' => now()->subDay(),
        'author_id' => $author->id,
    ]);

    $response = $this->get("/posts/{$post->slug}");

    $response->assertOk();
    $response->assertSee('"mainEntityOfPage":', false);
    $response->assertSee('"@type":"WebPage"', false);
});
