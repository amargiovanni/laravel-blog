<?php

declare(strict_types=1);

use App\Models\Category;
use App\Models\Media;
use App\Models\Post;
use App\Models\Setting;
use App\Models\User;
use App\Services\JsonLdService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->service = new JsonLdService;
});

test('generates BlogPosting schema for post', function (): void {
    $author = User::factory()->create(['name' => 'John Doe']);
    $post = Post::factory()->create([
        'title' => 'Test Blog Post',
        'content' => 'This is the post content.',
        'excerpt' => 'A test excerpt',
        'status' => 'published',
        'published_at' => now()->subDay(),
        'author_id' => $author->id,
    ]);

    $jsonLd = $this->service->forPost($post);

    expect($jsonLd['@context'])->toBe('https://schema.org');
    expect($jsonLd['@type'])->toBe('BlogPosting');
    expect($jsonLd['headline'])->toBe('Test Blog Post');
    expect($jsonLd['description'])->toBe('A test excerpt');
    expect($jsonLd['author']['@type'])->toBe('Person');
    expect($jsonLd['author']['name'])->toBe('John Doe');
});

test('includes publisher information', function (): void {
    config(['blog.name' => 'My Blog']);

    $author = User::factory()->create();
    $post = Post::factory()->create([
        'status' => 'published',
        'published_at' => now()->subDay(),
        'author_id' => $author->id,
    ]);

    $jsonLd = $this->service->forPost($post);

    expect($jsonLd['publisher']['@type'])->toBe('Organization');
    expect($jsonLd['publisher']['name'])->toBe('My Blog');
});

test('includes publisher logo from settings', function (): void {
    Setting::set('theme.logo', 'media/logo.png', 'theme');

    $author = User::factory()->create();
    $post = Post::factory()->create([
        'status' => 'published',
        'published_at' => now()->subDay(),
        'author_id' => $author->id,
    ]);

    $jsonLd = $this->service->forPost($post);

    expect($jsonLd['publisher']['logo']['@type'])->toBe('ImageObject');
    expect($jsonLd['publisher']['logo']['url'])->toContain('media/logo.png');
});

test('includes featured image when available', function (): void {
    $author = User::factory()->create();
    $media = Media::factory()->create([
        'path' => 'media/featured.jpg',
        'mime_type' => 'image/jpeg',
    ]);
    $post = Post::factory()->create([
        'status' => 'published',
        'published_at' => now()->subDay(),
        'author_id' => $author->id,
        'featured_image_id' => $media->id,
    ]);

    $jsonLd = $this->service->forPost($post);

    expect($jsonLd['image'])->toContain('media/featured.jpg');
});

test('includes datePublished and dateModified', function (): void {
    $author = User::factory()->create();
    $publishedAt = now()->subDays(5);
    $post = Post::factory()->create([
        'status' => 'published',
        'published_at' => $publishedAt,
        'author_id' => $author->id,
    ]);

    // Update the post to set updated_at
    $post->touch();

    $jsonLd = $this->service->forPost($post);

    expect($jsonLd['datePublished'])->toBe($publishedAt->toIso8601String());
    expect($jsonLd['dateModified'])->toBe($post->updated_at->toIso8601String());
});

test('includes mainEntityOfPage', function (): void {
    $author = User::factory()->create();
    $post = Post::factory()->create([
        'status' => 'published',
        'published_at' => now()->subDay(),
        'author_id' => $author->id,
    ]);

    $jsonLd = $this->service->forPost($post);

    expect($jsonLd['mainEntityOfPage']['@type'])->toBe('WebPage');
    expect($jsonLd['mainEntityOfPage']['@id'])->toContain("/posts/{$post->slug}");
});

test('truncates headline to 110 characters', function (): void {
    $author = User::factory()->create();
    $longTitle = str_repeat('A', 150);
    $post = Post::factory()->create([
        'title' => $longTitle,
        'status' => 'published',
        'published_at' => now()->subDay(),
        'author_id' => $author->id,
    ]);

    $jsonLd = $this->service->forPost($post);

    expect(strlen($jsonLd['headline']))->toBeLessThanOrEqual(110);
});

test('generates Organization schema for site', function (): void {
    config(['blog.name' => 'My Blog']);
    config(['app.url' => 'https://myblog.com']);

    $jsonLd = $this->service->forOrganization();

    expect($jsonLd['@context'])->toBe('https://schema.org');
    expect($jsonLd['@type'])->toBe('Organization');
    expect($jsonLd['name'])->toBe('My Blog');
    expect($jsonLd['url'])->toBe('https://myblog.com');
});

test('generates WebSite schema', function (): void {
    config(['blog.name' => 'My Blog']);
    config(['app.url' => 'https://myblog.com']);

    $jsonLd = $this->service->forWebsite();

    expect($jsonLd['@context'])->toBe('https://schema.org');
    expect($jsonLd['@type'])->toBe('WebSite');
    expect($jsonLd['name'])->toBe('My Blog');
    expect($jsonLd['url'])->toBe('https://myblog.com');
});

test('includes search action in website schema', function (): void {
    config(['app.url' => 'https://myblog.com']);

    $jsonLd = $this->service->forWebsite();

    expect($jsonLd['potentialAction']['@type'])->toBe('SearchAction');
    expect($jsonLd['potentialAction']['target']['urlTemplate'])->toContain('/search?q={search_term_string}');
});

test('returns empty array when jsonld is disabled', function (): void {
    Setting::set('geo.jsonld_enabled', false, 'geo');

    $author = User::factory()->create();
    $post = Post::factory()->create([
        'status' => 'published',
        'published_at' => now()->subDay(),
        'author_id' => $author->id,
    ]);

    $jsonLd = $this->service->forPost($post);

    expect($jsonLd)->toBeEmpty();
});

test('toScript method returns valid script tag', function (): void {
    $author = User::factory()->create();
    $post = Post::factory()->create([
        'status' => 'published',
        'published_at' => now()->subDay(),
        'author_id' => $author->id,
    ]);

    $script = $this->service->toScript($this->service->forPost($post));

    expect($script)->toStartWith('<script type="application/ld+json">');
    expect($script)->toEndWith('</script>');
    // JSON_UNESCAPED_SLASHES is used, so no escaping of slashes
    expect($script)->toContain('"@context":"https://schema.org"');
});

test('handles post without featured image', function (): void {
    $author = User::factory()->create();
    $post = Post::factory()->create([
        'status' => 'published',
        'published_at' => now()->subDay(),
        'author_id' => $author->id,
        'featured_image_id' => null,
    ]);

    $jsonLd = $this->service->forPost($post);

    expect($jsonLd)->not->toHaveKey('image');
});

test('includes article section from category', function (): void {
    $author = User::factory()->create();
    $category = Category::factory()->create(['name' => 'Technology']);
    $post = Post::factory()->create([
        'status' => 'published',
        'published_at' => now()->subDay(),
        'author_id' => $author->id,
    ]);
    $post->categories()->attach($category);

    $jsonLd = $this->service->forPost($post);

    expect($jsonLd['articleSection'])->toBe('Technology');
});
