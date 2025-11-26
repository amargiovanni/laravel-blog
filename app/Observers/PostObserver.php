<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Post;
use App\Services\RelatedPostsService;
use Illuminate\Support\Facades\Cache;

class PostObserver
{
    public function __construct(
        protected RelatedPostsService $relatedPostsService,
    ) {}

    /**
     * Handle the Post "created" event.
     */
    public function created(Post $post): void
    {
        // Clear llms.txt cache for new posts
        if ($post->status === 'published') {
            $this->clearLlmsTxtCache();
        }

        // Clear related posts cache for posts that might be related
        $this->clearRelatedPostsCache($post);

        activity()
            ->performedOn($post)
            ->causedBy($post->author)
            ->withProperties(['title' => $post->title, 'status' => $post->status])
            ->log('created');
    }

    /**
     * Handle the Post "updated" event.
     */
    public function updated(Post $post): void
    {
        $changes = $post->getChanges();
        unset($changes['updated_at']);

        if (empty($changes)) {
            return;
        }

        $description = 'updated';

        // Check for specific status changes
        if (isset($changes['status'])) {
            if ($changes['status'] === 'published') {
                $description = 'published';
            } elseif ($changes['status'] === 'draft') {
                $description = 'unpublished';
            } elseif ($changes['status'] === 'scheduled') {
                $description = 'scheduled';
            }
        }

        // Clear llms.txt cache when post status changes or title/content updates
        if ($post->isPublished() || isset($changes['status']) || isset($changes['title'])) {
            $this->clearLlmsTxtCache();
        }

        // Clear related posts cache when post is updated
        $this->clearRelatedPostsCache($post);

        activity()
            ->performedOn($post)
            ->causedBy(auth()->user())
            ->withProperties([
                'changes' => $changes,
                'old' => collect($post->getOriginal())->only(array_keys($changes))->toArray(),
            ])
            ->log($description);
    }

    /**
     * Handle the Post "deleted" event.
     */
    public function deleted(Post $post): void
    {
        // Clear llms.txt cache when published post is deleted
        if ($post->status === 'published') {
            $this->clearLlmsTxtCache();
        }

        activity()
            ->performedOn($post)
            ->causedBy(auth()->user())
            ->withProperties(['title' => $post->title])
            ->log('deleted');
    }

    /**
     * Handle the Post "restored" event.
     */
    public function restored(Post $post): void
    {
        activity()
            ->performedOn($post)
            ->causedBy(auth()->user())
            ->withProperties(['title' => $post->title])
            ->log('restored');
    }

    /**
     * Handle the Post "force deleted" event.
     */
    public function forceDeleted(Post $post): void
    {
        activity()
            ->causedBy(auth()->user())
            ->withProperties([
                'title' => $post->title,
                'id' => $post->id,
            ])
            ->log('permanently deleted post');
    }

    /**
     * Clear the llms.txt cache whenever a post changes.
     */
    protected function clearLlmsTxtCache(): void
    {
        Cache::forget('llms.txt');
    }

    /**
     * Clear related posts cache for a post and any posts that might be related.
     */
    protected function clearRelatedPostsCache(Post $post): void
    {
        // Clear cache for this post
        $this->relatedPostsService->clearCache($post);

        // Get posts that share tags or categories with this post
        // and clear their related posts cache too
        $relatedPostIds = collect();

        if ($post->tags->isNotEmpty()) {
            $tagIds = $post->tags->pluck('id');
            $relatedPostIds = $relatedPostIds->merge(
                Post::whereHas('tags', fn ($q) => $q->whereIn('tags.id', $tagIds))
                    ->where('id', '!=', $post->id)
                    ->pluck('id')
            );
        }

        if ($post->categories->isNotEmpty()) {
            $categoryIds = $post->categories->pluck('id');
            $relatedPostIds = $relatedPostIds->merge(
                Post::whereHas('categories', fn ($q) => $q->whereIn('categories.id', $categoryIds))
                    ->where('id', '!=', $post->id)
                    ->pluck('id')
            );
        }

        // Clear cache for related posts
        $relatedPostIds->unique()->each(function ($postId) {
            foreach ([3, 4, 5, 6] as $limit) {
                Cache::forget("related_posts:{$postId}:{$limit}");
            }
        });
    }
}
