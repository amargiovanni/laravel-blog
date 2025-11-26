<?php

declare(strict_types=1);

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use App\Services\RelatedPostsService;
use Illuminate\Support\Facades\Cache;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

beforeEach(function () {
    $this->author = User::factory()->create();
    $this->service = new RelatedPostsService;
});

describe('RelatedPostsService', function () {
    test('finds related posts by shared tags', function () {
        $tag = Tag::factory()->create(['name' => 'Laravel']);

        $post1 = Post::factory()->create([
            'title' => 'Main Post',
            'status' => 'published',
            'published_at' => now()->subDay(),
            'author_id' => $this->author->id,
        ]);
        $post1->tags()->attach($tag);

        $post2 = Post::factory()->create([
            'title' => 'Related By Tag',
            'status' => 'published',
            'published_at' => now()->subDays(2),
            'author_id' => $this->author->id,
        ]);
        $post2->tags()->attach($tag);

        $related = $this->service->getRelatedPosts($post1, 4, false);

        expect($related)->toHaveCount(1);
        expect($related->first()->title)->toBe('Related By Tag');
    });

    test('finds related posts by shared categories', function () {
        $category = Category::factory()->create(['name' => 'Web Development']);

        $post1 = Post::factory()->create([
            'title' => 'Main Post',
            'status' => 'published',
            'published_at' => now()->subDay(),
            'author_id' => $this->author->id,
        ]);
        $post1->categories()->attach($category);

        $post2 = Post::factory()->create([
            'title' => 'Related By Category',
            'status' => 'published',
            'published_at' => now()->subDays(2),
            'author_id' => $this->author->id,
        ]);
        $post2->categories()->attach($category);

        $related = $this->service->getRelatedPosts($post1, 4, false);

        expect($related)->toHaveCount(1);
        expect($related->first()->title)->toBe('Related By Category');
    });

    test('excludes current post from related posts', function () {
        $tag = Tag::factory()->create();

        $post = Post::factory()->create([
            'status' => 'published',
            'published_at' => now()->subDay(),
            'author_id' => $this->author->id,
        ]);
        $post->tags()->attach($tag);

        $related = $this->service->getRelatedPosts($post, 4, false);

        expect($related->pluck('id'))->not->toContain($post->id);
    });

    test('excludes draft posts from related posts', function () {
        $tag = Tag::factory()->create();

        $publishedPost = Post::factory()->create([
            'title' => 'Published Post',
            'status' => 'published',
            'published_at' => now()->subDay(),
            'author_id' => $this->author->id,
        ]);
        $publishedPost->tags()->attach($tag);

        $draftPost = Post::factory()->create([
            'title' => 'Draft Post',
            'status' => 'draft',
            'author_id' => $this->author->id,
        ]);
        $draftPost->tags()->attach($tag);

        $related = $this->service->getRelatedPosts($publishedPost, 4, false);

        expect($related->pluck('title'))->not->toContain('Draft Post');
    });

    test('weights tag matches higher than category matches', function () {
        $tag = Tag::factory()->create();
        $category = Category::factory()->create();

        $mainPost = Post::factory()->create([
            'title' => 'Main Post',
            'status' => 'published',
            'published_at' => now(),
            'author_id' => $this->author->id,
        ]);
        $mainPost->tags()->attach($tag);
        $mainPost->categories()->attach($category);

        // Post with same tag (should rank higher)
        $tagMatch = Post::factory()->create([
            'title' => 'Tag Match',
            'status' => 'published',
            'published_at' => now()->subMonth(),
            'author_id' => $this->author->id,
        ]);
        $tagMatch->tags()->attach($tag);

        // Post with same category only
        $categoryMatch = Post::factory()->create([
            'title' => 'Category Match',
            'status' => 'published',
            'published_at' => now()->subDay(),
            'author_id' => $this->author->id,
        ]);
        $categoryMatch->categories()->attach($category);

        $related = $this->service->getRelatedPosts($mainPost, 4, false);

        // Tag match should be first despite being older
        expect($related->first()->title)->toBe('Tag Match');
    });

    test('posts with multiple shared tags rank higher', function () {
        $tag1 = Tag::factory()->create();
        $tag2 = Tag::factory()->create();
        $tag3 = Tag::factory()->create();

        $mainPost = Post::factory()->create([
            'status' => 'published',
            'published_at' => now(),
            'author_id' => $this->author->id,
        ]);
        $mainPost->tags()->attach([$tag1->id, $tag2->id, $tag3->id]);

        // Post with 1 shared tag
        $oneTag = Post::factory()->create([
            'title' => 'One Tag',
            'status' => 'published',
            'published_at' => now()->subDay(),
            'author_id' => $this->author->id,
        ]);
        $oneTag->tags()->attach($tag1);

        // Post with 3 shared tags (should rank higher)
        $threeTags = Post::factory()->create([
            'title' => 'Three Tags',
            'status' => 'published',
            'published_at' => now()->subMonth(),
            'author_id' => $this->author->id,
        ]);
        $threeTags->tags()->attach([$tag1->id, $tag2->id, $tag3->id]);

        $related = $this->service->getRelatedPosts($mainPost, 4, false);

        expect($related->first()->title)->toBe('Three Tags');
    });

    test('limits results to specified count', function () {
        $tag = Tag::factory()->create();

        $mainPost = Post::factory()->create([
            'status' => 'published',
            'published_at' => now(),
            'author_id' => $this->author->id,
        ]);
        $mainPost->tags()->attach($tag);

        for ($i = 0; $i < 10; $i++) {
            $post = Post::factory()->create([
                'status' => 'published',
                'published_at' => now()->subDays($i + 1),
                'author_id' => $this->author->id,
            ]);
            $post->tags()->attach($tag);
        }

        $related = $this->service->getRelatedPosts($mainPost, 4, false);

        expect($related)->toHaveCount(4);
    });

    test('fills with recent posts when not enough related posts', function () {
        $tag = Tag::factory()->create();

        $mainPost = Post::factory()->create([
            'title' => 'Main Post',
            'status' => 'published',
            'published_at' => now(),
            'author_id' => $this->author->id,
        ]);
        $mainPost->tags()->attach($tag);

        // Only one related post
        $relatedPost = Post::factory()->create([
            'title' => 'Related Post',
            'status' => 'published',
            'published_at' => now()->subDay(),
            'author_id' => $this->author->id,
        ]);
        $relatedPost->tags()->attach($tag);

        // Unrelated posts that should fill remaining slots
        for ($i = 0; $i < 5; $i++) {
            Post::factory()->create([
                'title' => "Unrelated Post {$i}",
                'status' => 'published',
                'published_at' => now()->subDays($i + 2),
                'author_id' => $this->author->id,
            ]);
        }

        $related = $this->service->getRelatedPosts($mainPost, 4, false);

        expect($related)->toHaveCount(4);
        expect($related->first()->title)->toBe('Related Post');
    });

    test('returns empty collection when no posts available', function () {
        $post = Post::factory()->create([
            'status' => 'published',
            'published_at' => now(),
            'author_id' => $this->author->id,
        ]);

        $related = $this->service->getRelatedPosts($post, 4, false);

        expect($related)->toBeEmpty();
    });

    test('caches related posts results', function () {
        $tag = Tag::factory()->create();

        $post = Post::factory()->create([
            'status' => 'published',
            'published_at' => now(),
            'author_id' => $this->author->id,
        ]);
        $post->tags()->attach($tag);

        $relatedPost = Post::factory()->create([
            'status' => 'published',
            'published_at' => now()->subDay(),
            'author_id' => $this->author->id,
        ]);
        $relatedPost->tags()->attach($tag);

        // First call should cache
        $result1 = $this->service->getRelatedPosts($post, 4, true);

        // Verify it's cached
        expect(Cache::has("related_posts:{$post->id}:4"))->toBeTrue();

        // Second call should use cache
        $result2 = $this->service->getRelatedPosts($post, 4, true);

        expect($result1->pluck('id')->toArray())->toBe($result2->pluck('id')->toArray());
    });

    test('clearCache removes cached results', function () {
        $post = Post::factory()->create([
            'status' => 'published',
            'published_at' => now(),
            'author_id' => $this->author->id,
        ]);

        // Populate cache
        $this->service->getRelatedPosts($post, 4, true);
        expect(Cache::has("related_posts:{$post->id}:4"))->toBeTrue();

        // Clear cache
        $this->service->clearCache($post);

        expect(Cache::has("related_posts:{$post->id}:4"))->toBeFalse();
    });
});

describe('Related Posts Component', function () {
    test('related posts section appears on post page', function () {
        $tag = Tag::factory()->create();

        $mainPost = Post::factory()->create([
            'title' => 'Main Post',
            'status' => 'published',
            'published_at' => now()->subDay(),
            'author_id' => $this->author->id,
        ]);
        $mainPost->tags()->attach($tag);

        $relatedPost = Post::factory()->create([
            'title' => 'Related Post Title',
            'status' => 'published',
            'published_at' => now()->subDays(2),
            'author_id' => $this->author->id,
        ]);
        $relatedPost->tags()->attach($tag);

        $response = $this->get(route('posts.show', $mainPost->slug));

        $response->assertOk();
        $response->assertSee('Related Posts');
        $response->assertSee('Related Post Title');
    });

    test('related posts section is hidden when no related posts', function () {
        $post = Post::factory()->create([
            'title' => 'Lonely Post',
            'status' => 'published',
            'published_at' => now()->subDay(),
            'author_id' => $this->author->id,
        ]);

        $response = $this->get(route('posts.show', $post->slug));

        $response->assertOk();
        $response->assertDontSee('Related Posts');
    });
});
