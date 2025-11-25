<?php

declare(strict_types=1);

use App\Models\Category;
use App\Models\Post;
use App\Models\Setting;
use App\Models\User;
use App\Services\LlmsTxtService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->service = new LlmsTxtService;
});

test('generates llms.txt with site header', function (): void {
    config(['blog.name' => 'Test Blog']);
    config(['blog.description' => 'A test blog for unit testing']);

    $content = $this->service->generate();

    expect($content)
        ->toContain('# Test Blog')
        ->toContain('> A test blog for unit testing');
});

test('includes published posts section', function (): void {
    $author = User::factory()->create();
    $post = Post::factory()->create([
        'title' => 'Published Test Post',
        'status' => 'published',
        'published_at' => now()->subDay(),
        'author_id' => $author->id,
    ]);

    $content = $this->service->generate();

    expect($content)
        ->toContain('## Blog Posts')
        ->toContain('Published Test Post')
        ->toContain("/posts/{$post->slug}");
});

test('excludes draft posts', function (): void {
    $author = User::factory()->create();
    Post::factory()->create([
        'title' => 'Draft Post Should Not Appear',
        'status' => 'draft',
        'author_id' => $author->id,
    ]);

    $content = $this->service->generate();

    expect($content)->not->toContain('Draft Post Should Not Appear');
});

test('excludes scheduled posts', function (): void {
    $author = User::factory()->create();
    Post::factory()->create([
        'title' => 'Scheduled Post Should Not Appear',
        'status' => 'scheduled',
        'published_at' => now()->addWeek(),
        'author_id' => $author->id,
    ]);

    $content = $this->service->generate();

    expect($content)->not->toContain('Scheduled Post Should Not Appear');
});

test('includes categories section', function (): void {
    $category = Category::factory()->create([
        'name' => 'Technology',
    ]);

    $content = $this->service->generate();

    expect($content)
        ->toContain('## Categories')
        ->toContain('Technology')
        ->toContain("/categories/{$category->slug}");
});

test('respects geo settings for posts inclusion', function (): void {
    Setting::set('geo.llms_include_posts', false, 'geo');

    $author = User::factory()->create();
    Post::factory()->create([
        'title' => 'Should Not Appear',
        'status' => 'published',
        'published_at' => now()->subDay(),
        'author_id' => $author->id,
    ]);

    $content = $this->service->generate();

    expect($content)->not->toContain('## Blog Posts');
});

test('returns empty string when llms is disabled', function (): void {
    Setting::set('geo.llms_enabled', false, 'geo');

    $content = $this->service->generate();

    expect($content)->toBe('');
});

test('generates valid markdown format', function (): void {
    $author = User::factory()->create();
    Post::factory()->create([
        'title' => 'Test Post',
        'status' => 'published',
        'published_at' => now()->subDay(),
        'author_id' => $author->id,
    ]);

    $content = $this->service->generate();

    // Should start with H1
    expect($content)->toMatch('/^# .+$/m');

    // Links should be in markdown format
    expect($content)->toMatch('/\[.+\]\(https?:\/\/.+\)/');
});

test('limits posts to configured amount', function (): void {
    $author = User::factory()->create();

    // Create more posts than the limit
    Post::factory()->count(25)->create([
        'status' => 'published',
        'published_at' => now()->subDay(),
        'author_id' => $author->id,
    ]);

    $content = $this->service->generate();

    // Default limit is 20 posts
    $matches = [];
    preg_match_all('/- \[.+\]\(.+\)/', $content, $matches);
    $postLinks = array_filter($matches[0], fn ($link) => str_contains($link, '/posts/'));

    expect(count($postLinks))->toBeLessThanOrEqual(20);
});

test('validates generated content format', function (): void {
    $author = User::factory()->create();
    Post::factory()->create([
        'title' => 'Test Post',
        'status' => 'published',
        'published_at' => now()->subDay(),
        'author_id' => $author->id,
    ]);

    $validation = $this->service->validate();

    expect($validation['valid'])->toBeTrue();
    expect($validation['errors'])->toBeEmpty();
});

test('returns validation errors for malformed content', function (): void {
    // Force empty content
    Setting::set('geo.llms_enabled', false, 'geo');

    $validation = $this->service->validate();

    expect($validation['valid'])->toBeFalse();
    expect($validation['errors'])->not->toBeEmpty();
});
