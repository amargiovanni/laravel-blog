<?php

declare(strict_types=1);

use App\Models\Page;
use App\Models\Post;
use App\Models\Revision;
use App\Models\User;
use App\Services\RevisionService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

describe('HasRevisions trait', function (): void {
    it('creates a revision when a post is created', function (): void {
        $post = Post::factory()->create([
            'title' => 'Test Post',
            'content' => 'Test content',
            'author_id' => $this->user->id,
        ]);

        expect($post->revisions()->count())->toBe(1);

        $revision = $post->revisions()->first();
        expect($revision->title)->toBe('Test Post');
        expect($revision->content)->toBe('Test content');
        expect($revision->revision_number)->toBe(1);
        expect($revision->user_id)->toBe($this->user->id);
    });

    it('creates a revision when content fields are updated', function (): void {
        $post = Post::factory()->create([
            'title' => 'Original Title',
            'content' => 'Original content',
            'author_id' => $this->user->id,
        ]);

        $post->update([
            'title' => 'Updated Title',
            'content' => 'Updated content',
        ]);

        expect($post->revisions()->count())->toBe(2);

        $latestRevision = $post->getLatestRevision();
        expect($latestRevision->title)->toBe('Updated Title');
        expect($latestRevision->revision_number)->toBe(2);
    });

    it('does not create a revision when non-tracked fields are updated', function (): void {
        $post = Post::factory()->create([
            'title' => 'Test Post',
            'author_id' => $this->user->id,
        ]);

        // Refresh to clear wasRecentlyCreated flag
        $post = $post->fresh();
        $initialCount = $post->revisions()->count();

        // Use a field that is not in the tracked fields array
        $post->update(['meta_title' => 'New Meta Title']);

        expect($post->revisions()->count())->toBe($initialCount);
    });

    it('can create manual revisions', function (): void {
        $post = Post::factory()->create(['author_id' => $this->user->id]);
        $initialCount = $post->revisions()->count();

        $revision = $post->createRevision();

        expect($post->fresh()->revisions()->count())->toBe($initialCount + 1);
        expect($revision->is_autosave)->toBeFalse();
    });

    it('can create autosave revisions', function (): void {
        $post = Post::factory()->create(['author_id' => $this->user->id]);

        $revision = $post->createRevision(isAutosave: true);

        expect($revision->is_autosave)->toBeTrue();
    });

    it('increments revision numbers correctly', function (): void {
        $post = Post::factory()->create(['author_id' => $this->user->id]);

        $post->createRevision();
        $post->createRevision();
        $post->createRevision();

        // Use reorder to override the default descending order
        $revisions = $post->revisions()->reorder('revision_number', 'asc')->get();

        expect($revisions->pluck('revision_number')->toArray())->toBe([1, 2, 3, 4]);
    });

    it('can get revision by number', function (): void {
        $post = Post::factory()->create(['author_id' => $this->user->id]);

        $post->update(['title' => 'Second version']);
        $post->update(['title' => 'Third version']);

        $revision = $post->getRevisionByNumber(2);
        expect($revision->title)->toBe('Second version');
    });

    it('returns revision count', function (): void {
        $post = Post::factory()->create(['author_id' => $this->user->id]);
        $post->createRevision();
        $post->createRevision();

        expect($post->getRevisionCount())->toBe(3);
    });
});

describe('Revision model', function (): void {
    it('belongs to a user', function (): void {
        $revision = Revision::factory()
            ->byUser($this->user)
            ->create();

        expect($revision->user->id)->toBe($this->user->id);
    });

    it('can be morphed to different models', function (): void {
        $post = Post::factory()->create(['author_id' => $this->user->id]);
        $page = Page::factory()->create(['author_id' => $this->user->id]);

        expect($post->revisions()->count())->toBeGreaterThan(0);
        expect($page->revisions()->count())->toBeGreaterThan(0);

        $postRevision = $post->revisions()->first();
        $pageRevision = $page->revisions()->first();

        expect($postRevision->revisionable)->toBeInstanceOf(Post::class);
        expect($pageRevision->revisionable)->toBeInstanceOf(Page::class);
    });

    it('can get author name', function (): void {
        $revision = Revision::factory()
            ->byUser($this->user)
            ->create();

        expect($revision->getAuthorName())->toBe($this->user->name);
    });

    it('returns unknown for author name when no user', function (): void {
        $revision = Revision::factory()->create(['user_id' => null]);

        expect($revision->getAuthorName())->toBe('Unknown');
    });

    it('can access metadata values', function (): void {
        $revision = Revision::factory()->create([
            'metadata' => [
                'status' => 'published',
                'slug' => 'test-slug',
            ],
        ]);

        expect($revision->getMetadata('status'))->toBe('published');
        expect($revision->getMetadata('slug'))->toBe('test-slug');
        expect($revision->getMetadata('nonexistent', 'default'))->toBe('default');
    });

    it('casts metadata to array', function (): void {
        $revision = Revision::factory()->create([
            'metadata' => ['key' => 'value'],
        ]);

        expect($revision->metadata)->toBeArray();
    });

    it('casts booleans correctly', function (): void {
        $revision = Revision::factory()
            ->autosave()
            ->protected()
            ->create();

        expect($revision->is_autosave)->toBeTrue();
        expect($revision->is_protected)->toBeTrue();
        expect($revision->isAutosave())->toBeTrue();
        expect($revision->isProtected())->toBeTrue();
    });
});

describe('restoring revisions', function (): void {
    it('can restore a post to a previous revision', function (): void {
        $post = Post::factory()->create([
            'title' => 'Original Title',
            'content' => 'Original content',
            'excerpt' => 'Original excerpt',
            'author_id' => $this->user->id,
        ]);

        $originalRevision = $post->revisions()->first();

        $post->update([
            'title' => 'Updated Title',
            'content' => 'Updated content',
        ]);

        $post->restoreToRevision($originalRevision);

        expect($post->fresh()->title)->toBe('Original Title');
        expect($post->fresh()->content)->toBe('Original content');
    });

    it('restores metadata from revision', function (): void {
        $post = Post::factory()->create([
            'title' => 'Test',
            'slug' => 'original-slug',
            'author_id' => $this->user->id,
        ]);

        $originalRevision = $post->revisions()->first();

        $post->update(['slug' => 'new-slug']);

        $post->restoreToRevision($originalRevision);

        expect($post->fresh()->slug)->toBe('original-slug');
    });
});

describe('RevisionService', function (): void {
    it('can get revisions excluding autosaves', function (): void {
        $service = app(RevisionService::class);
        $post = Post::factory()->create(['author_id' => $this->user->id]);

        $post->createRevision(isAutosave: true);
        $post->createRevision(isAutosave: false);

        $revisionsWithoutAutosaves = $service->getRevisions($post, includeAutosaves: false);
        $revisionsWithAutosaves = $service->getRevisions($post, includeAutosaves: true);

        expect($revisionsWithoutAutosaves->where('is_autosave', true)->count())->toBe(0);
        expect($revisionsWithAutosaves->where('is_autosave', true)->count())->toBe(1);
    });

    it('can get a specific revision', function (): void {
        $service = app(RevisionService::class);
        $post = Post::factory()->create([
            'title' => 'Version 1',
            'author_id' => $this->user->id,
        ]);

        $post->update(['title' => 'Version 2']);

        $revision = $service->getRevision($post, 1);

        expect($revision->title)->toBe('Version 1');
    });

    it('generates diff between revisions', function (): void {
        $service = app(RevisionService::class);

        $revision1 = Revision::factory()->create([
            'title' => 'Old Title',
            'content' => 'Old content here',
        ]);

        $revision2 = Revision::factory()->create([
            'title' => 'New Title',
            'content' => 'New content here',
            'revisionable_type' => $revision1->revisionable_type,
            'revisionable_id' => $revision1->revisionable_id,
        ]);

        $diff = $service->getDiff($revision1, $revision2);

        expect($diff)->toHaveKeys(['title', 'content', 'excerpt']);
        expect($diff['title'])->not->toBeEmpty();
        expect($diff['content'])->not->toBeEmpty();
    });

    it('returns empty string for identical content', function (): void {
        $service = app(RevisionService::class);

        $diff = $service->generateDiff('Same content', 'Same content');

        expect($diff)->toBe('');
    });

    it('can protect and unprotect revisions', function (): void {
        $service = app(RevisionService::class);
        $revision = Revision::factory()->create(['is_protected' => false]);

        $service->protectRevision($revision);
        expect($revision->fresh()->is_protected)->toBeTrue();

        $service->unprotectRevision($revision);
        expect($revision->fresh()->is_protected)->toBeFalse();
    });

    it('cannot delete protected revisions', function (): void {
        $service = app(RevisionService::class);
        $revision = Revision::factory()
            ->protected()
            ->create();

        $result = $service->deleteRevision($revision);

        expect($result)->toBeFalse();
        expect(Revision::find($revision->id))->not->toBeNull();
    });

    it('can delete unprotected revisions', function (): void {
        $service = app(RevisionService::class);
        $revision = Revision::factory()->create(['is_protected' => false]);

        $result = $service->deleteRevision($revision);

        expect($result)->toBeTrue();
        expect(Revision::find($revision->id))->toBeNull();
    });

    it('can cleanup old revisions keeping specified count', function (): void {
        $service = app(RevisionService::class);
        $post = Post::factory()->create(['author_id' => $this->user->id]);

        // Create 10 additional revisions
        for ($i = 0; $i < 10; $i++) {
            $post->createRevision();
        }

        expect($post->revisions()->count())->toBe(11);

        $deleted = $service->cleanupOldRevisions($post, keepCount: 5);

        expect($deleted)->toBe(6);
        expect($post->revisions()->count())->toBe(5);
    });

    it('does not delete protected revisions during cleanup', function (): void {
        $service = app(RevisionService::class);
        $post = Post::factory()->create(['author_id' => $this->user->id]);

        // Create revisions and protect some
        for ($i = 0; $i < 5; $i++) {
            $revision = $post->createRevision();
            if ($i < 3) {
                $revision->update(['is_protected' => true]);
            }
        }

        $service->cleanupOldRevisions($post, keepCount: 2);

        // Protected revisions should still exist
        expect($post->revisions()->where('is_protected', true)->count())->toBe(3);
    });

    it('can cleanup old autosaves', function (): void {
        $service = app(RevisionService::class);
        $post = Post::factory()->create(['author_id' => $this->user->id]);

        // Create autosaves
        for ($i = 0; $i < 10; $i++) {
            $post->createRevision(isAutosave: true);
        }

        $deleted = $service->cleanupAutosaves($post, keepCount: 3);

        expect($deleted)->toBe(7);
        expect($post->revisions()->where('is_autosave', true)->count())->toBe(3);
    });
});

describe('Page revisions', function (): void {
    it('creates revisions for pages', function (): void {
        $page = Page::factory()->create([
            'title' => 'Test Page',
            'content' => 'Test content',
            'author_id' => $this->user->id,
        ]);

        expect($page->revisions()->count())->toBe(1);

        $revision = $page->revisions()->first();
        expect($revision->title)->toBe('Test Page');
        expect($revision->revisionable_type)->toBe(Page::class);
    });

    it('can restore a page to a previous revision', function (): void {
        $page = Page::factory()->create([
            'title' => 'Original Page',
            'content' => 'Original content',
            'author_id' => $this->user->id,
        ]);

        $originalRevision = $page->revisions()->first();

        $page->update([
            'title' => 'Updated Page',
            'content' => 'Updated content',
        ]);

        $page->restoreToRevision($originalRevision);

        expect($page->fresh()->title)->toBe('Original Page');
        expect($page->fresh()->content)->toBe('Original content');
    });
});

describe('RevisionFactory', function (): void {
    it('creates revisions with factory', function (): void {
        $revision = Revision::factory()->create();

        expect($revision)->toBeInstanceOf(Revision::class);
        expect($revision->title)->not->toBeEmpty();
        expect($revision->revision_number)->toBe(1);
    });

    it('can create revision for specific post', function (): void {
        $post = Post::factory()->create(['author_id' => $this->user->id]);
        $revision = Revision::factory()->forPost($post)->create();

        expect($revision->revisionable_id)->toBe($post->id);
        expect($revision->revisionable_type)->toBe(Post::class);
        expect($revision->title)->toBe($post->title);
    });

    it('can create autosave revision', function (): void {
        $revision = Revision::factory()->autosave()->create();

        expect($revision->is_autosave)->toBeTrue();
    });

    it('can create protected revision', function (): void {
        $revision = Revision::factory()->protected()->create();

        expect($revision->is_protected)->toBeTrue();
    });

    it('can set revision number', function (): void {
        $revision = Revision::factory()->revisionNumber(5)->create();

        expect($revision->revision_number)->toBe(5);
    });
});
