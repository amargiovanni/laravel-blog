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

test('posts list page exists', function (): void {
    $this->get('/posts')
        ->assertSuccessful();
});

test('posts list shows only published posts', function (): void {
    $user = User::factory()->author()->create();

    $publishedPost = Post::factory()->for($user, 'author')->published()->create([
        'title' => 'Published Blog Post',
    ]);

    $draftPost = Post::factory()->for($user, 'author')->draft()->create([
        'title' => 'Draft Blog Post',
    ]);

    $this->get('/posts')
        ->assertSuccessful()
        ->assertSee('Published Blog Post')
        ->assertDontSee('Draft Blog Post');
});

test('single post page shows post content', function (): void {
    $user = User::factory()->author()->create();

    $post = Post::factory()->for($user, 'author')->published()->create([
        'title' => 'Test Blog Post',
        'content' => 'This is the blog post content.',
        'slug' => 'test-blog-post',
    ]);

    $this->get('/posts/test-blog-post')
        ->assertSuccessful()
        ->assertSee('Test Blog Post')
        ->assertSee('This is the blog post content.');
});

test('single post page shows author name', function (): void {
    $user = User::factory()->author()->create(['name' => 'John Author']);

    $post = Post::factory()->for($user, 'author')->published()->create([
        'slug' => 'author-post',
    ]);

    $this->get('/posts/author-post')
        ->assertSuccessful()
        ->assertSee('John Author');
});

test('single post page shows categories', function (): void {
    $user = User::factory()->author()->create();
    $category = Category::factory()->create(['name' => 'Tech News']);

    $post = Post::factory()->for($user, 'author')->published()->create([
        'slug' => 'categorized-post',
    ]);
    $post->categories()->attach($category);

    $this->get('/posts/categorized-post')
        ->assertSuccessful()
        ->assertSee('Tech News');
});

test('single post page shows tags', function (): void {
    $user = User::factory()->author()->create();
    $tag = Tag::factory()->create(['name' => 'Laravel']);

    $post = Post::factory()->for($user, 'author')->published()->create([
        'slug' => 'tagged-post',
    ]);
    $post->tags()->attach($tag);

    $this->get('/posts/tagged-post')
        ->assertSuccessful()
        ->assertSee('Laravel');
});

test('draft posts are not accessible', function (): void {
    $user = User::factory()->author()->create();

    $post = Post::factory()->for($user, 'author')->draft()->create([
        'slug' => 'draft-post',
    ]);

    $this->get('/posts/draft-post')
        ->assertNotFound();
});

test('scheduled posts are not accessible until published', function (): void {
    $user = User::factory()->author()->create();

    $post = Post::factory()->for($user, 'author')->scheduled()->create([
        'slug' => 'scheduled-post',
    ]);

    $this->get('/posts/scheduled-post')
        ->assertNotFound();
});

test('posts can be filtered by category', function (): void {
    $user = User::factory()->author()->create();
    $category = Category::factory()->create(['name' => 'News', 'slug' => 'news']);
    $otherCategory = Category::factory()->create(['name' => 'Reviews', 'slug' => 'reviews']);

    $newsPost = Post::factory()->for($user, 'author')->published()->create([
        'title' => 'News Post',
    ]);
    $newsPost->categories()->attach($category);

    $reviewPost = Post::factory()->for($user, 'author')->published()->create([
        'title' => 'Review Post',
    ]);
    $reviewPost->categories()->attach($otherCategory);

    $this->get('/category/news')
        ->assertSuccessful()
        ->assertSee('News Post')
        ->assertDontSee('Review Post');
});

test('posts can be filtered by tag', function (): void {
    $user = User::factory()->author()->create();
    $tag = Tag::factory()->create(['name' => 'PHP', 'slug' => 'php']);
    $otherTag = Tag::factory()->create(['name' => 'JavaScript', 'slug' => 'javascript']);

    $phpPost = Post::factory()->for($user, 'author')->published()->create([
        'title' => 'PHP Tutorial',
    ]);
    $phpPost->tags()->attach($tag);

    $jsPost = Post::factory()->for($user, 'author')->published()->create([
        'title' => 'JS Tutorial',
    ]);
    $jsPost->tags()->attach($otherTag);

    $this->get('/tag/php')
        ->assertSuccessful()
        ->assertSee('PHP Tutorial')
        ->assertDontSee('JS Tutorial');
});

test('viewing a post increments view count', function (): void {
    $user = User::factory()->author()->create();

    $post = Post::factory()->for($user, 'author')->published()->create([
        'slug' => 'view-count-post',
        'view_count' => 0,
    ]);

    expect($post->view_count)->toBe(0);

    $this->get('/posts/view-count-post');

    expect($post->fresh()->view_count)->toBe(1);
});
