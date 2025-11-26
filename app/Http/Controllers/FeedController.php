<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Http\Response;
use Spatie\Feed\Feed;

class FeedController extends Controller
{
    /**
     * Generate RSS feed for a specific category.
     */
    public function category(string $slug): Response
    {
        $category = Category::where('slug', $slug)->firstOrFail();

        $posts = Post::query()
            ->published()
            ->whereHas('categories', fn ($q) => $q->where('categories.id', $category->id))
            ->with(['author', 'categories', 'tags', 'featuredImage'])
            ->latest('published_at')
            ->take(20)
            ->get();

        return $this->buildFeedResponse(
            title: $category->name.' - '.config('blog.name', config('app.name')).' RSS Feed',
            items: $posts,
            url: route('categories.show.feed', $category->slug),
            description: $category->description ?? "Posts in {$category->name} category",
        );
    }

    /**
     * Generate RSS feed for a specific author.
     */
    public function author(int|string $id): Response
    {
        $author = User::findOrFail($id);

        $posts = Post::query()
            ->published()
            ->where('author_id', $author->id)
            ->with(['author', 'categories', 'tags', 'featuredImage'])
            ->latest('published_at')
            ->take(20)
            ->get();

        return $this->buildFeedResponse(
            title: $author->name.' - '.config('blog.name', config('app.name')).' RSS Feed',
            items: $posts,
            url: route('authors.feed', $author->id),
            description: "Posts by {$author->name}",
        );
    }

    /**
     * Generate RSS feed for a specific tag.
     */
    public function tag(string $slug): Response
    {
        $tag = Tag::where('slug', $slug)->firstOrFail();

        $posts = Post::query()
            ->published()
            ->whereHas('tags', fn ($q) => $q->where('tags.id', $tag->id))
            ->with(['author', 'categories', 'tags', 'featuredImage'])
            ->latest('published_at')
            ->take(20)
            ->get();

        return $this->buildFeedResponse(
            title: $tag->name.' - '.config('blog.name', config('app.name')).' RSS Feed',
            items: $posts,
            url: route('tags.show.feed', $tag->slug),
            description: $tag->description ?? "Posts tagged with {$tag->name}",
        );
    }

    /**
     * Build and return an RSS feed response.
     *
     * @param  \Illuminate\Support\Collection<int, Post>  $items
     */
    protected function buildFeedResponse(
        string $title,
        $items,
        string $url,
        string $description,
    ): Response {
        $feed = new Feed(
            title: $title,
            items: $items,
            url: $url,
            view: 'feed::rss',
            description: $description,
            language: config('app.locale', 'en'),
            format: 'rss',
        );

        return $feed->toResponse(request());
    }
}
