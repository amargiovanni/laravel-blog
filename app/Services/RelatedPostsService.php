<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Post;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class RelatedPostsService
{
    /**
     * Cache TTL in seconds (1 hour).
     */
    protected const int CACHE_TTL = 3600;

    /**
     * Weight multiplier for tag matches.
     */
    protected const int TAG_WEIGHT = 3;

    /**
     * Weight multiplier for category matches.
     */
    protected const int CATEGORY_WEIGHT = 1;

    /**
     * Recency bonus factor - posts within this many days get a boost.
     */
    protected const int RECENCY_DAYS = 30;

    /**
     * Get related posts for a given post.
     *
     * @return Collection<int, Post>
     */
    public function getRelatedPosts(Post $post, int $limit = 4, bool $useCache = true): Collection
    {
        $cacheKey = $this->getCacheKey($post, $limit);

        if ($useCache) {
            return Cache::remember($cacheKey, self::CACHE_TTL, fn () => $this->findRelatedPosts($post, $limit));
        }

        return $this->findRelatedPosts($post, $limit);
    }

    /**
     * Clear the cache for a specific post.
     */
    public function clearCache(Post $post): void
    {
        // Clear cache for common limit values
        foreach ([3, 4, 5, 6] as $limit) {
            Cache::forget($this->getCacheKey($post, $limit));
        }
    }

    /**
     * Clear all related posts cache.
     */
    public function clearAllCache(): void
    {
        Cache::flush();
    }

    /**
     * Find related posts using relevance scoring.
     *
     * @return Collection<int, Post>
     */
    protected function findRelatedPosts(Post $post, int $limit): Collection
    {
        $tagIds = $post->tags->pluck('id')->toArray();
        $categoryIds = $post->categories->pluck('id')->toArray();

        // Get candidate posts (any post that shares a tag or category)
        $candidates = Post::query()
            ->published()
            ->where('id', '!=', $post->id)
            ->where(function ($query) use ($tagIds, $categoryIds) {
                if (count($tagIds) > 0) {
                    $query->whereHas('tags', fn ($q) => $q->whereIn('tags.id', $tagIds));
                }
                if (count($categoryIds) > 0) {
                    $query->orWhereHas('categories', fn ($q) => $q->whereIn('categories.id', $categoryIds));
                }
            })
            ->with(['tags', 'categories', 'featuredImage', 'author'])
            ->get();

        // Score and rank candidates
        $scoredPosts = $candidates->map(function (Post $candidate) use ($tagIds, $categoryIds) {
            $score = $this->calculateRelevanceScore($candidate, $tagIds, $categoryIds);

            return [
                'post' => $candidate,
                'score' => $score,
            ];
        });

        // Sort by score (descending), then by date (newest first)
        $sorted = $scoredPosts->sortBy([
            ['score', 'desc'],
            fn ($a, $b) => $b['post']->published_at <=> $a['post']->published_at,
        ])->take($limit);

        $relatedPosts = $sorted->pluck('post');

        // If we don't have enough related posts, fill with recent posts
        if ($relatedPosts->count() < $limit) {
            $relatedPosts = $this->fillWithRecentPosts($relatedPosts, $post, $limit);
        }

        return $relatedPosts->values();
    }

    /**
     * Calculate relevance score for a candidate post.
     */
    protected function calculateRelevanceScore(Post $candidate, array $tagIds, array $categoryIds): float
    {
        // Count shared tags
        $sharedTags = $candidate->tags->pluck('id')->intersect($tagIds)->count();

        // Count shared categories
        $sharedCategories = $candidate->categories->pluck('id')->intersect($categoryIds)->count();

        // Base score: (shared_tags × 3) + (shared_categories × 1)
        $score = ($sharedTags * self::TAG_WEIGHT) + ($sharedCategories * self::CATEGORY_WEIGHT);

        // Add recency bonus for recent posts
        $score += $this->calculateRecencyBonus($candidate);

        return $score;
    }

    /**
     * Calculate recency bonus for a post.
     * Posts published within RECENCY_DAYS get a bonus up to 1.0.
     */
    protected function calculateRecencyBonus(Post $candidate): float
    {
        if (! $candidate->published_at) {
            return 0;
        }

        $daysSincePublished = $candidate->published_at->diffInDays(now());

        if ($daysSincePublished >= self::RECENCY_DAYS) {
            return 0;
        }

        // Linear decay: 1.0 for today, 0.0 at RECENCY_DAYS
        return 1.0 - ($daysSincePublished / self::RECENCY_DAYS);
    }

    /**
     * Fill remaining slots with recent posts if needed.
     *
     * @param  Collection<int, Post>  $relatedPosts
     * @return Collection<int, Post>
     */
    protected function fillWithRecentPosts(Collection $relatedPosts, Post $currentPost, int $limit): Collection
    {
        $needed = $limit - $relatedPosts->count();

        if ($needed <= 0) {
            return $relatedPosts;
        }

        $excludeIds = $relatedPosts->pluck('id')->push($currentPost->id)->toArray();

        $recentPosts = Post::query()
            ->published()
            ->whereNotIn('id', $excludeIds)
            ->with(['tags', 'categories', 'featuredImage', 'author'])
            ->latest('published_at')
            ->take($needed)
            ->get();

        return $relatedPosts->concat($recentPosts);
    }

    /**
     * Generate cache key for a post.
     */
    protected function getCacheKey(Post $post, int $limit): string
    {
        return "related_posts:{$post->id}:{$limit}";
    }
}
