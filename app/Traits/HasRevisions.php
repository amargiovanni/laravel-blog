<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\Revision;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasRevisions
{
    public static function bootHasRevisions(): void
    {
        static::saved(function ($model): void {
            if ($model->shouldCreateRevision()) {
                $model->createRevision();
            }
        });
    }

    /**
     * @return MorphMany<Revision, $this>
     */
    public function revisions(): MorphMany
    {
        return $this->morphMany(Revision::class, 'revisionable')->orderByDesc('revision_number');
    }

    public function createRevision(bool $isAutosave = false): Revision
    {
        $lastRevision = $this->revisions()->first();
        $revisionNumber = $lastRevision ? $lastRevision->revision_number + 1 : 1;

        return $this->revisions()->create([
            'user_id' => auth()->id(),
            'revision_number' => $revisionNumber,
            'title' => $this->getRevisionTitle(),
            'content' => $this->getRevisionContent(),
            'excerpt' => $this->getRevisionExcerpt(),
            'metadata' => $this->getRevisionMetadata(),
            'is_autosave' => $isAutosave,
        ]);
    }

    public function getLatestRevision(): ?Revision
    {
        return $this->revisions()->first();
    }

    public function getRevisionByNumber(int $number): ?Revision
    {
        return $this->revisions()->where('revision_number', $number)->first();
    }

    public function getRevisionCount(): int
    {
        return $this->revisions()->count();
    }

    public function restoreToRevision(Revision $revision): self
    {
        $this->fill([
            'title' => $revision->title,
            'content' => $revision->content,
            'excerpt' => $revision->excerpt,
        ]);

        // Restore metadata
        $metadata = $revision->metadata ?? [];

        if (isset($metadata['slug'])) {
            $this->slug = $metadata['slug'];
        }

        if (isset($metadata['featured_image_id'])) {
            $this->featured_image_id = $metadata['featured_image_id'];
        }

        $this->save();

        // Restore relationships
        if (isset($metadata['category_ids']) && method_exists($this, 'categories')) {
            $this->categories()->sync($metadata['category_ids']);
        }

        if (isset($metadata['tag_ids']) && method_exists($this, 'tags')) {
            $this->tags()->sync($metadata['tag_ids']);
        }

        return $this;
    }

    protected function shouldCreateRevision(): bool
    {
        if ($this->wasRecentlyCreated) {
            return true;
        }

        // Check if content-related fields have changed
        $trackedFields = $this->getRevisionTrackedFields();

        return $this->wasChanged($trackedFields);
    }

    protected function getRevisionTrackedFields(): array
    {
        return ['title', 'content', 'excerpt'];
    }

    protected function getRevisionTitle(): string
    {
        return $this->title ?? '';
    }

    protected function getRevisionContent(): ?string
    {
        return $this->content;
    }

    protected function getRevisionExcerpt(): ?string
    {
        return $this->excerpt;
    }

    protected function getRevisionMetadata(): array
    {
        $metadata = [];

        if (isset($this->status)) {
            $metadata['status'] = $this->status;
        }

        if (isset($this->slug)) {
            $metadata['slug'] = $this->slug;
        }

        if (isset($this->featured_image_id)) {
            $metadata['featured_image_id'] = $this->featured_image_id;
        }

        if (method_exists($this, 'categories') && $this->relationLoaded('categories')) {
            $metadata['category_ids'] = $this->categories->pluck('id')->toArray();
        }

        if (method_exists($this, 'tags') && $this->relationLoaded('tags')) {
            $metadata['tag_ids'] = $this->tags->pluck('id')->toArray();
        }

        return $metadata;
    }
}
