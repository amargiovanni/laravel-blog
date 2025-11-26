<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Page;
use App\Models\Post;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class SearchService
{
    /**
     * Minimum query length required.
     */
    public const int MIN_QUERY_LENGTH = 2;

    /**
     * Maximum query length allowed.
     */
    public const int MAX_QUERY_LENGTH = 100;

    /**
     * Default number of results per page.
     */
    public const int PER_PAGE = 10;

    /**
     * Default excerpt length for search results.
     */
    public const int EXCERPT_LENGTH = 200;

    /**
     * Search across posts and pages.
     *
     * @return LengthAwarePaginator<\Illuminate\Database\Eloquent\Model>
     */
    public function search(string $query, int $perPage = self::PER_PAGE): LengthAwarePaginator
    {
        $query = $this->sanitizeQuery($query);

        if (mb_strlen($query) < self::MIN_QUERY_LENGTH) {
            return new LengthAwarePaginator([], 0, $perPage);
        }

        // Search posts using custom query for category/tag support
        $posts = $this->searchPosts($query);

        // Search pages using Scout
        $pages = Page::search($query)
            ->query(fn ($builder) => $builder->with(['author', 'featuredImage']))
            ->get()
            ->map(fn (Page $page) => $this->transformPageResult($page, $query));

        // Merge and sort by relevance (title matches first, then by date)
        $results = $posts->concat($pages)
            ->sortByDesc(function ($item) use ($query) {
                $score = 0;

                // Title match gets highest priority
                if (stripos($item['title'], $query) !== false) {
                    $score += 100;
                }

                // Exact title match gets even higher priority
                if (strcasecmp($item['title'], $query) === 0) {
                    $score += 50;
                }

                // Add recency score (newer content scores higher)
                if ($item['published_at']) {
                    $score += ($item['published_at']->timestamp / 1000000000);
                }

                return $score;
            })
            ->values();

        // Paginate manually
        $page = request()->input('page', 1);
        $offset = ($page - 1) * $perPage;

        return new LengthAwarePaginator(
            $results->slice($offset, $perPage)->values(),
            $results->count(),
            $perPage,
            $page,
            ['path' => request()->url(), 'query' => request()->query()]
        );
    }

    /**
     * Get search suggestions for autocomplete.
     *
     * @return Collection<int, array{title: string, url: string, type: string}>
     */
    public function suggest(string $query, int $limit = 5): Collection
    {
        $query = $this->sanitizeQuery($query);

        if (mb_strlen($query) < self::MIN_QUERY_LENGTH) {
            return collect();
        }

        $searchTerm = '%'.$query.'%';

        $posts = Post::query()
            ->published()
            ->where('title', 'like', $searchTerm)
            ->limit($limit)
            ->get()
            ->map(fn (Post $post) => [
                'title' => $post->title,
                'url' => route('posts.show', $post->slug),
                'type' => 'post',
            ]);

        $pages = Page::search($query)
            ->take($limit)
            ->get()
            ->map(fn (Page $page) => [
                'title' => $page->title,
                'url' => route('pages.show', $page->slug),
                'type' => 'page',
            ]);

        return $posts->concat($pages)
            ->take($limit)
            ->values();
    }

    /**
     * Sanitize search query.
     */
    public function sanitizeQuery(string $query): string
    {
        // Trim whitespace
        $query = trim($query);

        // Limit length
        $query = Str::limit($query, self::MAX_QUERY_LENGTH, '');

        // Remove potentially harmful characters
        $query = htmlspecialchars($query, ENT_QUOTES, 'UTF-8');

        return $query;
    }

    /**
     * Validate search query.
     */
    public function isValidQuery(string $query): bool
    {
        $query = trim($query);

        return mb_strlen($query) >= self::MIN_QUERY_LENGTH
            && mb_strlen($query) <= self::MAX_QUERY_LENGTH;
    }

    /**
     * Generate highlighted excerpt from content.
     */
    public function highlightExcerpt(string $content, string $query, int $length = self::EXCERPT_LENGTH): string
    {
        // Strip HTML tags
        $content = strip_tags($content);

        // Find the position of the query in content
        $queryLower = mb_strtolower($query);
        $contentLower = mb_strtolower($content);
        $position = mb_strpos($contentLower, $queryLower);

        if ($position !== false) {
            // Calculate start position to show context around the match
            $start = max(0, $position - (int) ($length / 4));
            $excerpt = mb_substr($content, $start, $length);

            // Add ellipsis if needed
            if ($start > 0) {
                $excerpt = '...'.ltrim($excerpt);
            }
            if (mb_strlen($content) > $start + $length) {
                $excerpt = rtrim($excerpt).'...';
            }
        } else {
            // No match found, return beginning of content
            $excerpt = Str::limit($content, $length);
        }

        // Highlight the query terms
        $excerpt = $this->highlightTerms($excerpt, $query);

        return $excerpt;
    }

    /**
     * Highlight search terms in text.
     */
    public function highlightTerms(string $text, string $query): string
    {
        // Split query into words for multi-word highlighting
        $terms = array_filter(explode(' ', $query));

        foreach ($terms as $term) {
            if (mb_strlen($term) >= 2) {
                $pattern = '/('.preg_quote($term, '/').')/iu';
                $text = preg_replace($pattern, '<mark class="bg-yellow-200 dark:bg-yellow-800 px-0.5 rounded">$1</mark>', $text) ?? $text;
            }
        }

        return $text;
    }

    /**
     * Search posts with support for categories and tags.
     *
     * @return Collection<int, array<string, mixed>>
     */
    protected function searchPosts(string $query): Collection
    {
        $searchTerm = '%'.$query.'%';

        return Post::query()
            ->published()
            ->with(['author', 'categories', 'tags', 'featuredImage'])
            ->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', $searchTerm)
                    ->orWhere('content', 'like', $searchTerm)
                    ->orWhere('excerpt', 'like', $searchTerm)
                    ->orWhereHas('categories', fn ($cq) => $cq->where('name', 'like', $searchTerm))
                    ->orWhereHas('tags', fn ($tq) => $tq->where('name', 'like', $searchTerm));
            })
            ->latest('published_at')
            ->get()
            ->map(fn (Post $post) => $this->transformPostResult($post, $query));
    }

    /**
     * Transform a Post to a search result array.
     *
     * @return array{id: int, title: string, excerpt: string, url: string, type: string, published_at: \Illuminate\Support\Carbon|null, model: Post, image: string|null, author: string|null, categories: Collection<int, string>}
     */
    protected function transformPostResult(Post $post, string $query): array
    {
        return [
            'id' => $post->id,
            'title' => $post->title,
            'excerpt' => $this->highlightExcerpt($post->content ?? '', $query),
            'url' => route('posts.show', $post->slug),
            'type' => 'post',
            'published_at' => $post->published_at,
            'model' => $post,
            'image' => $post->featuredImage?->url,
            'author' => $post->author?->name,
            'categories' => $post->categories->pluck('name'),
        ];
    }

    /**
     * Transform a Page to a search result array.
     *
     * @return array{id: int, title: string, excerpt: string, url: string, type: string, published_at: \Illuminate\Support\Carbon|null, model: Page, image: string|null, author: string|null, categories: Collection<int, string>}
     */
    protected function transformPageResult(Page $page, string $query): array
    {
        return [
            'id' => $page->id,
            'title' => $page->title,
            'excerpt' => $this->highlightExcerpt($page->content ?? '', $query),
            'url' => route('pages.show', $page->slug),
            'type' => 'page',
            'published_at' => $page->published_at,
            'model' => $page,
            'image' => $page->featuredImage?->url,
            'author' => $page->author?->name,
            'categories' => collect(),
        ];
    }
}
