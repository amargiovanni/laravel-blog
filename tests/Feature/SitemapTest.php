<?php

declare(strict_types=1);

use App\Models\Category;
use App\Models\Page;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use App\Services\SitemapService;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->author = User::factory()->create();
});

describe('Sitemap Route', function () {
    test('sitemap.xml is accessible', function () {
        $response = $this->get('/sitemap.xml');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/xml');
    });

    test('sitemap contains valid XML structure', function () {
        $response = $this->get('/sitemap.xml');

        $response->assertOk();
        $content = $response->getContent();

        // Check for XML declaration
        expect($content)->toContain('<?xml version="1.0" encoding="UTF-8"?>');

        // Check for urlset root element
        expect($content)->toContain('<urlset');
        expect($content)->toContain('</urlset>');
    });

    test('sitemap includes homepage', function () {
        $response = $this->get('/sitemap.xml');

        $response->assertOk();
        $content = $response->getContent();

        expect($content)->toContain(url('/'));
    });

    test('sitemap includes published posts', function () {
        $post = Post::factory()->create([
            'title' => 'Test Post',
            'slug' => 'test-post',
            'status' => 'published',
            'published_at' => now()->subDay(),
            'author_id' => $this->author->id,
        ]);

        $response = $this->get('/sitemap.xml');

        $response->assertOk();
        $content = $response->getContent();

        expect($content)->toContain(route('posts.show', $post->slug));
    });

    test('sitemap excludes draft posts', function () {
        $post = Post::factory()->create([
            'title' => 'Draft Post',
            'slug' => 'draft-post',
            'status' => 'draft',
            'author_id' => $this->author->id,
        ]);

        $response = $this->get('/sitemap.xml');

        $response->assertOk();
        $content = $response->getContent();

        expect($content)->not->toContain(route('posts.show', $post->slug));
    });

    test('sitemap includes published pages', function () {
        $page = Page::factory()->create([
            'title' => 'Test Page',
            'slug' => 'test-page',
            'status' => 'published',
            'published_at' => now()->subDay(),
            'author_id' => $this->author->id,
        ]);

        $response = $this->get('/sitemap.xml');

        $response->assertOk();
        $content = $response->getContent();

        expect($content)->toContain(route('pages.show', $page->slug));
    });

    test('sitemap includes categories with posts', function () {
        $category = Category::factory()->create(['slug' => 'test-category']);
        $post = Post::factory()->create([
            'status' => 'published',
            'published_at' => now()->subDay(),
            'author_id' => $this->author->id,
        ]);
        $post->categories()->attach($category);

        $response = $this->get('/sitemap.xml');

        $response->assertOk();
        $content = $response->getContent();

        expect($content)->toContain(route('categories.show', $category->slug));
    });

    test('sitemap excludes categories without posts', function () {
        $category = Category::factory()->create(['slug' => 'empty-category']);

        $response = $this->get('/sitemap.xml');

        $response->assertOk();
        $content = $response->getContent();

        expect($content)->not->toContain(route('categories.show', $category->slug));
    });

    test('sitemap includes tags with posts', function () {
        $tag = Tag::factory()->create(['slug' => 'test-tag']);
        $post = Post::factory()->create([
            'status' => 'published',
            'published_at' => now()->subDay(),
            'author_id' => $this->author->id,
        ]);
        $post->tags()->attach($tag);

        $response = $this->get('/sitemap.xml');

        $response->assertOk();
        $content = $response->getContent();

        expect($content)->toContain(route('tags.show', $tag->slug));
    });
});

describe('Robots.txt Route', function () {
    test('robots.txt is accessible', function () {
        $response = $this->get('/robots.txt');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/plain; charset=UTF-8');
    });

    test('robots.txt contains sitemap reference', function () {
        $response = $this->get('/robots.txt');

        $response->assertOk();
        $content = $response->getContent();

        expect($content)->toContain('Sitemap:');
        expect($content)->toContain(url('sitemap.xml'));
    });

    test('robots.txt allows crawling', function () {
        $response = $this->get('/robots.txt');

        $response->assertOk();
        $content = $response->getContent();

        expect($content)->toContain('User-agent: *');
        expect($content)->toContain('Allow: /');
    });

    test('robots.txt disallows admin and auth routes', function () {
        $response = $this->get('/robots.txt');

        $response->assertOk();
        $content = $response->getContent();

        expect($content)->toContain('Disallow: /admin/');
        expect($content)->toContain('Disallow: /login');
        expect($content)->toContain('Disallow: /dashboard');
    });
});

describe('SitemapService', function () {
    test('generates sitemap with homepage', function () {
        $service = new SitemapService;
        $xml = $service->toXml();

        expect($xml)->toContain(url('/'));
    });

    test('generates sitemap with blog index', function () {
        $service = new SitemapService;
        $xml = $service->toXml();

        expect($xml)->toContain(route('posts.index'));
    });

    test('includes priority and changefreq', function () {
        $service = new SitemapService;
        $xml = $service->toXml();

        expect($xml)->toContain('<priority>');
        expect($xml)->toContain('<changefreq>');
    });

    test('includes lastmod for posts', function () {
        Post::factory()->create([
            'status' => 'published',
            'published_at' => now()->subDay(),
            'author_id' => User::factory()->create()->id,
        ]);

        $service = new SitemapService;
        $xml = $service->toXml();

        expect($xml)->toContain('<lastmod>');
    });
});

describe('GenerateSitemap Command', function () {
    test('command generates sitemap file', function () {
        $path = storage_path('test-sitemap.xml');

        $this->artisan('sitemap:generate', ['--path' => $path])
            ->assertSuccessful();

        expect(file_exists($path))->toBeTrue();

        // Clean up
        @unlink($path);
    });

    test('command outputs url count', function () {
        $this->artisan('sitemap:generate', ['--path' => storage_path('test-sitemap.xml')])
            ->expectsOutputToContain('Total URLs in sitemap:')
            ->assertSuccessful();

        // Clean up
        @unlink(storage_path('test-sitemap.xml'));
    });
});
