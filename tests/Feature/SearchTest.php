<?php

declare(strict_types=1);

use App\Models\Category;
use App\Models\Page;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use App\Services\SearchService;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->author = User::factory()->create();
});

describe('Search Results Page', function () {
    test('search page is accessible', function () {
        $response = $this->get(route('search'));

        $response->assertOk();
        $response->assertSee('Search');
    });

    test('search page shows initial state without query', function () {
        $response = $this->get(route('search'));

        $response->assertOk();
        $response->assertSee('Search the blog');
        $response->assertSee('Enter keywords to search through posts and pages');
    });

    test('search page shows validation error for short query', function () {
        $response = $this->get(route('search', ['q' => 'a']));

        $response->assertOk();
        $response->assertSee('Please enter at least 2 characters to search');
    });

    test('search returns matching posts', function () {
        Post::factory()->create([
            'title' => 'Laravel Tutorial Guide',
            'content' => 'This is a Laravel tutorial',
            'status' => 'published',
            'published_at' => now()->subDay(),
            'author_id' => $this->author->id,
        ]);

        // Verify search service finds results
        $service = new SearchService;
        $results = $service->search('Laravel');
        expect($results->total())->toBe(1);
        expect($results->first()['title'])->toBe('Laravel Tutorial Guide');
    });

    test('search returns matching pages', function () {
        Page::factory()->create([
            'title' => 'About Our Company',
            'content' => 'We are a software company',
            'status' => 'published',
            'published_at' => now()->subDay(),
            'author_id' => $this->author->id,
        ]);

        // Verify search service finds results
        $service = new SearchService;
        $results = $service->search('Company');
        expect($results->total())->toBe(1);
        expect($results->first()['title'])->toBe('About Our Company');
    });

    test('search shows no results message when nothing found', function () {
        $response = $this->get(route('search', ['q' => 'nonexistentquery123']));

        $response->assertOk();
        $response->assertSee('No results found');
        $response->assertSee('Try different keywords');
    });

    test('search excludes draft posts', function () {
        $draftPost = Post::factory()->create([
            'title' => 'Draft Post Title',
            'content' => 'Draft content',
            'status' => 'draft',
            'author_id' => $this->author->id,
        ]);

        $response = $this->get(route('search', ['q' => 'Draft']));

        $response->assertOk();
        $response->assertDontSee('Draft Post Title');
    });

    test('search excludes unpublished pages', function () {
        $draftPage = Page::factory()->create([
            'title' => 'Draft Page About',
            'content' => 'Draft page content',
            'status' => 'draft',
            'author_id' => $this->author->id,
        ]);

        $response = $this->get(route('search', ['q' => 'Draft Page']));

        $response->assertOk();
        $response->assertDontSee('Draft Page About');
    });
});

describe('SearchService', function () {
    test('sanitizes query by trimming whitespace', function () {
        $service = new SearchService;

        expect($service->sanitizeQuery('  hello world  '))->toBe('hello world');
    });

    test('sanitizes query by limiting length', function () {
        $service = new SearchService;
        $longQuery = str_repeat('a', 150);

        $sanitized = $service->sanitizeQuery($longQuery);

        expect(strlen($sanitized))->toBeLessThanOrEqual(SearchService::MAX_QUERY_LENGTH);
    });

    test('validates query returns false for short queries', function () {
        $service = new SearchService;

        expect($service->isValidQuery('a'))->toBeFalse();
        expect($service->isValidQuery(''))->toBeFalse();
    });

    test('validates query returns true for valid queries', function () {
        $service = new SearchService;

        expect($service->isValidQuery('ab'))->toBeTrue();
        expect($service->isValidQuery('hello world'))->toBeTrue();
    });

    test('highlights terms in text', function () {
        $service = new SearchService;

        $result = $service->highlightTerms('Laravel is a PHP framework', 'Laravel');

        expect($result)->toContain('<mark');
        expect($result)->toContain('Laravel');
        expect($result)->toContain('</mark>');
    });

    test('highlights multiple terms', function () {
        $service = new SearchService;

        $result = $service->highlightTerms('Laravel PHP framework', 'Laravel PHP');

        expect(substr_count($result, '<mark'))->toBe(2);
    });

    test('generates excerpt with context around match', function () {
        $service = new SearchService;
        $content = str_repeat('Lorem ipsum ', 50).'important keyword here '.str_repeat('dolor sit ', 50);

        $excerpt = $service->highlightExcerpt($content, 'keyword', 100);

        expect($excerpt)->toContain('keyword');
        expect($excerpt)->toContain('...');
    });

    test('search finds posts by title', function () {
        $post = Post::factory()->create([
            'title' => 'Unique Test Title XYZ',
            'content' => 'Some content',
            'status' => 'published',
            'published_at' => now()->subDay(),
            'author_id' => User::factory()->create()->id,
        ]);

        $service = new SearchService;
        $results = $service->search('XYZ');

        expect($results->total())->toBe(1);
        expect($results->first()['title'])->toBe('Unique Test Title XYZ');
    });

    test('search finds posts by content', function () {
        $post = Post::factory()->create([
            'title' => 'Regular Title',
            'content' => 'This contains uniquecontent123 in the body',
            'status' => 'published',
            'published_at' => now()->subDay(),
            'author_id' => User::factory()->create()->id,
        ]);

        $service = new SearchService;
        $results = $service->search('uniquecontent123');

        expect($results->total())->toBe(1);
    });

    test('search ranks title matches higher', function () {
        $author = User::factory()->create();

        $postWithTitleMatch = Post::factory()->create([
            'title' => 'Laravel Tutorial',
            'content' => 'Some generic content',
            'status' => 'published',
            'published_at' => now()->subDay(),
            'author_id' => $author->id,
        ]);

        $postWithContentMatch = Post::factory()->create([
            'title' => 'Generic Title',
            'content' => 'This content mentions Laravel somewhere',
            'status' => 'published',
            'published_at' => now()->subDays(2),
            'author_id' => $author->id,
        ]);

        $service = new SearchService;
        $results = $service->search('Laravel');

        // Title match should be first
        expect($results->first()['title'])->toBe('Laravel Tutorial');
    });
});

describe('Search with Categories and Tags', function () {
    test('search finds posts by category name', function () {
        $category = Category::factory()->create(['name' => 'Web Development']);
        $post = Post::factory()->create([
            'title' => 'Some Post',
            'content' => 'Content without search terms',
            'status' => 'published',
            'published_at' => now()->subDay(),
            'author_id' => User::factory()->create()->id,
        ]);
        $post->categories()->attach($category);

        // Re-index the post to include category
        $post->searchable();

        $service = new SearchService;
        $results = $service->search('Development');

        expect($results->total())->toBeGreaterThanOrEqual(1);
    });

    test('search finds posts by tag name', function () {
        $tag = Tag::factory()->create(['name' => 'UniqueTag123']);
        $post = Post::factory()->create([
            'title' => 'Tagged Post',
            'content' => 'Content without the tag word',
            'status' => 'published',
            'published_at' => now()->subDay(),
            'author_id' => User::factory()->create()->id,
        ]);
        $post->tags()->attach($tag);

        // Re-index the post to include tag
        $post->searchable();

        $service = new SearchService;
        $results = $service->search('UniqueTag123');

        expect($results->total())->toBeGreaterThanOrEqual(1);
    });
});

describe('Search Suggestions', function () {
    test('suggest returns matching titles', function () {
        Post::factory()->create([
            'title' => 'Laravel Best Practices',
            'status' => 'published',
            'published_at' => now()->subDay(),
            'author_id' => User::factory()->create()->id,
        ]);

        $service = new SearchService;
        $suggestions = $service->suggest('Laravel');

        expect($suggestions)->toHaveCount(1);
        expect($suggestions->first()['title'])->toBe('Laravel Best Practices');
        expect($suggestions->first()['type'])->toBe('post');
    });

    test('suggest limits results', function () {
        $author = User::factory()->create();

        for ($i = 1; $i <= 10; $i++) {
            Post::factory()->create([
                'title' => "Test Post Number {$i}",
                'status' => 'published',
                'published_at' => now()->subDay(),
                'author_id' => $author->id,
            ]);
        }

        $service = new SearchService;
        $suggestions = $service->suggest('Test', 5);

        expect($suggestions)->toHaveCount(5);
    });

    test('suggest returns empty for short queries', function () {
        $service = new SearchService;
        $suggestions = $service->suggest('a');

        expect($suggestions)->toBeEmpty();
    });
});
