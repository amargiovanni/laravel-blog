# PostObserver Responsibilities

> This document defines the consolidated responsibilities for `app/Observers/PostObserver.php` across all features.

## Overview

The `PostObserver` handles model events for the `Post` model. Multiple features require hooks into post lifecycle events. Rather than creating separate observers, all post-related observation logic is consolidated into a single observer with clearly separated concerns.

## Implementation Order

Features should be implemented in this order to properly build up the PostObserver:

1. **006-post-revisions** (P1) - Creates the initial PostObserver
2. **011-related-posts** (P2) - Adds cache invalidation hooks
3. **012-newsletter** (P2) - Adds new post notification trigger
4. **014-redirect-manager** (P2) - Adds slug change detection

## Event Handlers

### `creating(Post $post)`

| Feature | Action | Priority |
|---------|--------|----------|
| - | No current requirements | - |

### `created(Post $post)`

| Feature | Action | Priority |
|---------|--------|----------|
| 006-post-revisions | Create initial revision | P1 |

### `updating(Post $post)`

| Feature | Action | Priority |
|---------|--------|----------|
| 014-redirect-manager | Detect slug change, store old slug | P2 |

### `updated(Post $post)`

| Feature | Action | Priority |
|---------|--------|----------|
| 006-post-revisions | Create revision snapshot | P1 |
| 011-related-posts | Invalidate related posts cache if tags/categories changed | P2 |
| 014-redirect-manager | Create 301 redirect if slug changed | P2 |
| 012-newsletter | Dispatch new post notification if just published | P2 |

### `deleted(Post $post)`

| Feature | Action | Priority |
|---------|--------|----------|
| 006-post-revisions | Delete associated revisions (cascade) | P1 |
| 011-related-posts | Invalidate related posts cache | P2 |

## Code Structure

```php
<?php

namespace App\Observers;

use App\Models\Post;
use App\Services\RevisionService;
use App\Services\RelatedPostsService;
use App\Services\RedirectService;
use App\Jobs\SendNewPostNotificationJob;

class PostObserver
{
    public function __construct(
        private RevisionService $revisionService,
        private RelatedPostsService $relatedPostsService,
        private RedirectService $redirectService,
    ) {}

    /**
     * Handle the Post "created" event.
     * Features: 006-post-revisions
     */
    public function created(Post $post): void
    {
        // 006: Create initial revision
        $this->revisionService->createRevision($post);
    }

    /**
     * Handle the Post "updating" event.
     * Features: 014-redirect-manager
     */
    public function updating(Post $post): void
    {
        // 014: Store old slug for redirect creation
        if ($post->isDirty('slug')) {
            $post->old_slug = $post->getOriginal('slug');
        }
    }

    /**
     * Handle the Post "updated" event.
     * Features: 006-post-revisions, 011-related-posts, 012-newsletter, 014-redirect-manager
     */
    public function updated(Post $post): void
    {
        // 006: Create revision snapshot
        $this->revisionService->createRevision($post);

        // 011: Invalidate related posts cache if tags/categories changed
        if ($post->wasChanged(['categories', 'tags'])) {
            $this->relatedPostsService->clearCache($post);
        }

        // 014: Create redirect if slug changed
        if (isset($post->old_slug) && $post->old_slug !== $post->slug) {
            $this->redirectService->createAutomaticRedirect(
                $post->old_slug,
                $post->slug
            );
        }

        // 012: Send new post notification if just published
        if ($post->wasChanged('status') && $post->status === 'published') {
            if (config('newsletter.notify_on_publish', true)) {
                SendNewPostNotificationJob::dispatch($post);
            }
        }
    }

    /**
     * Handle the Post "deleted" event.
     * Features: 006-post-revisions, 011-related-posts
     */
    public function deleted(Post $post): void
    {
        // 006: Revisions are cascade deleted via foreign key

        // 011: Invalidate related posts cache
        $this->relatedPostsService->clearCache($post);
    }
}
```

## Registration

Register in `app/Providers/AppServiceProvider.php`:

```php
use App\Models\Post;
use App\Observers\PostObserver;

public function boot(): void
{
    Post::observe(PostObserver::class);
}
```

## Testing Strategy

Each feature should test its specific PostObserver functionality:

| Feature | Test File | What to Test |
|---------|-----------|--------------|
| 006-post-revisions | `tests/Feature/RevisionTest.php` | Revision created on save |
| 011-related-posts | `tests/Feature/RelatedPostsCacheTest.php` | Cache cleared on tag change |
| 012-newsletter | `tests/Feature/NewPostNotificationTest.php` | Notification dispatched on publish |
| 014-redirect-manager | `tests/Feature/AutoRedirectTest.php` | Redirect created on slug change |

## Notes

- The observer uses constructor injection for services
- Each method is clearly commented with the feature that added it
- Services handle the actual business logic; observer just dispatches
- Use `$post->isDirty()` in `updating` and `$post->wasChanged()` in `updated`
- Feature flags in config control optional behaviors (e.g., newsletter notifications)
