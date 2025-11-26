<?php

declare(strict_types=1);

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->author = User::factory()->create([
        'name' => 'Test Author',
        'email' => 'author@example.com',
    ]);
});

describe('Main RSS Feed', function () {
    test('main feed returns valid RSS', function () {
        Post::factory()->create([
            'title' => 'Published Post',
            'content' => 'Test content here',
            'status' => 'published',
            'published_at' => now()->subDay(),
            'author_id' => $this->author->id,
        ]);

        $response = $this->get('/feed');

        $response->assertOk();
        $response->assertSee('<?xml version="1.0" encoding="UTF-8"?>', false);
        $response->assertSee('<rss', false);
        $response->assertSee('Published Post');
    });

    test('main feed contains correct metadata', function () {
        $response = $this->get('/feed');

        $response->assertOk();
        $response->assertSee('<channel>', false);
        $response->assertSee('<title>', false);
        $response->assertSee('<link>', false);
        $response->assertSee('<description>', false);
    });

    test('main feed excludes draft posts', function () {
        Post::factory()->create([
            'title' => 'Draft Post Title',
            'status' => 'draft',
            'author_id' => $this->author->id,
        ]);

        $response = $this->get('/feed');

        $response->assertOk();
        $response->assertDontSee('Draft Post Title');
    });

    test('main feed excludes scheduled posts', function () {
        Post::factory()->create([
            'title' => 'Scheduled Post Title',
            'status' => 'scheduled',
            'published_at' => now()->addWeek(),
            'author_id' => $this->author->id,
        ]);

        $response = $this->get('/feed');

        $response->assertOk();
        $response->assertDontSee('Scheduled Post Title');
    });

    test('main feed includes author information', function () {
        Post::factory()->create([
            'title' => 'Post With Author',
            'status' => 'published',
            'published_at' => now()->subDay(),
            'author_id' => $this->author->id,
        ]);

        $response = $this->get('/feed');

        $response->assertOk();
        $response->assertSee('Test Author');
    });

    test('main feed limits to 20 items', function () {
        Post::factory()->count(25)->create([
            'status' => 'published',
            'published_at' => now()->subDay(),
            'author_id' => $this->author->id,
        ]);

        $response = $this->get('/feed');

        $response->assertOk();
        $content = $response->getContent();
        $itemCount = substr_count($content, '<item>');
        expect($itemCount)->toBe(20);
    });

    test('main feed orders posts by published date descending', function () {
        Post::factory()->create([
            'title' => 'Older Post',
            'status' => 'published',
            'published_at' => now()->subDays(5),
            'author_id' => $this->author->id,
        ]);

        Post::factory()->create([
            'title' => 'Newer Post',
            'status' => 'published',
            'published_at' => now()->subDay(),
            'author_id' => $this->author->id,
        ]);

        $response = $this->get('/feed');

        $response->assertOk();
        $content = $response->getContent();
        $newerPosition = strpos($content, 'Newer Post');
        $olderPosition = strpos($content, 'Older Post');
        expect($newerPosition)->toBeLessThan($olderPosition);
    });

    test('main feed includes categories as categories', function () {
        $category = Category::factory()->create(['name' => 'Technology']);
        $post = Post::factory()->create([
            'title' => 'Categorized Post',
            'status' => 'published',
            'published_at' => now()->subDay(),
            'author_id' => $this->author->id,
        ]);
        $post->categories()->attach($category);

        $response = $this->get('/feed');

        $response->assertOk();
        $response->assertSee('Technology');
    });

    test('main feed includes tags as categories', function () {
        $tag = Tag::factory()->create(['name' => 'Laravel']);
        $post = Post::factory()->create([
            'title' => 'Tagged Post',
            'status' => 'published',
            'published_at' => now()->subDay(),
            'author_id' => $this->author->id,
        ]);
        $post->tags()->attach($tag);

        $response = $this->get('/feed');

        $response->assertOk();
        $response->assertSee('Laravel');
    });

    test('main feed returns empty feed when no published posts', function () {
        $response = $this->get('/feed');

        $response->assertOk();
        $response->assertSee('<channel>', false);
    });
});

describe('Category RSS Feed', function () {
    test('category feed returns valid RSS', function () {
        $category = Category::factory()->create(['name' => 'Web Development', 'slug' => 'web-development']);
        $post = Post::factory()->create([
            'title' => 'Web Dev Post',
            'status' => 'published',
            'published_at' => now()->subDay(),
            'author_id' => $this->author->id,
        ]);
        $post->categories()->attach($category);

        $response = $this->get('/category/web-development/feed');

        $response->assertOk();
        $response->assertSee('Web Dev Post');
        $response->assertSee('Web Development');
    });

    test('category feed returns 404 for non-existent category', function () {
        $response = $this->get('/category/non-existent/feed');

        $response->assertNotFound();
    });

    test('category feed only includes posts from that category', function () {
        $category1 = Category::factory()->create(['slug' => 'cat-one']);
        $category2 = Category::factory()->create(['slug' => 'cat-two']);

        $post1 = Post::factory()->create([
            'title' => 'Post In Cat One',
            'status' => 'published',
            'published_at' => now()->subDay(),
            'author_id' => $this->author->id,
        ]);
        $post1->categories()->attach($category1);

        $post2 = Post::factory()->create([
            'title' => 'Post In Cat Two',
            'status' => 'published',
            'published_at' => now()->subDay(),
            'author_id' => $this->author->id,
        ]);
        $post2->categories()->attach($category2);

        $response = $this->get('/category/cat-one/feed');

        $response->assertOk();
        $response->assertSee('Post In Cat One');
        $response->assertDontSee('Post In Cat Two');
    });
});

describe('Tag RSS Feed', function () {
    test('tag feed returns valid RSS', function () {
        $tag = Tag::factory()->create(['name' => 'PHP', 'slug' => 'php']);
        $post = Post::factory()->create([
            'title' => 'PHP Tutorial',
            'status' => 'published',
            'published_at' => now()->subDay(),
            'author_id' => $this->author->id,
        ]);
        $post->tags()->attach($tag);

        $response = $this->get('/tag/php/feed');

        $response->assertOk();
        $response->assertSee('PHP Tutorial');
    });

    test('tag feed returns 404 for non-existent tag', function () {
        $response = $this->get('/tag/non-existent/feed');

        $response->assertNotFound();
    });
});

describe('Author RSS Feed', function () {
    test('author feed returns valid RSS', function () {
        $author = User::factory()->create([
            'name' => 'John Doe',
        ]);

        Post::factory()->create([
            'title' => 'John Post',
            'status' => 'published',
            'published_at' => now()->subDay(),
            'author_id' => $author->id,
        ]);

        $response = $this->get("/author/{$author->id}/feed");

        $response->assertOk();
        $response->assertSee('John Post');
        $response->assertSee('John Doe');
    });

    test('author feed returns 404 for non-existent author', function () {
        $response = $this->get('/author/99999/feed');

        $response->assertNotFound();
    });

    test('author feed only includes posts from that author', function () {
        $author1 = User::factory()->create(['name' => 'Author One']);
        $author2 = User::factory()->create(['name' => 'Author Two']);

        Post::factory()->create([
            'title' => 'Post By Author1',
            'status' => 'published',
            'published_at' => now()->subDay(),
            'author_id' => $author1->id,
        ]);

        Post::factory()->create([
            'title' => 'Post By Author2',
            'status' => 'published',
            'published_at' => now()->subDay(),
            'author_id' => $author2->id,
        ]);

        $response = $this->get("/author/{$author1->id}/feed");

        $response->assertOk();
        $response->assertSee('Post By Author1');
        $response->assertDontSee('Post By Author2');
    });
});

describe('RSS Feed Discovery', function () {
    test('blog layout includes RSS auto-discovery link', function () {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('type="application/rss+xml"', false);
        $response->assertSee('/feed', false);
    });
});
