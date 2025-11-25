<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Revision;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Jfcherng\Diff\Differ;
use Jfcherng\Diff\DiffHelper;

class RevisionService
{
    /**
     * @return Collection<int, Revision>
     */
    public function getRevisions(Model $model, bool $includeAutosaves = false): Collection
    {
        $query = $model->revisions();

        if (! $includeAutosaves) {
            $query->where('is_autosave', false);
        }

        return $query->with('user')->get();
    }

    public function getRevision(Model $model, int $revisionNumber): ?Revision
    {
        return $model->revisions()
            ->where('revision_number', $revisionNumber)
            ->with('user')
            ->first();
    }

    public function createRevision(Model $model, bool $isAutosave = false): Revision
    {
        return $model->createRevision($isAutosave);
    }

    public function createAutosave(Model $model): Revision
    {
        return $this->createRevision($model, true);
    }

    /**
     * @return array{title: string, content: string, excerpt: string}
     */
    public function getDiff(Revision $from, Revision $to): array
    {
        return [
            'title' => $this->generateDiff($from->title, $to->title),
            'content' => $this->generateDiff($from->content ?? '', $to->content ?? ''),
            'excerpt' => $this->generateDiff($from->excerpt ?? '', $to->excerpt ?? ''),
        ];
    }

    /**
     * @return array{title: string, content: string, excerpt: string}
     */
    public function getDiffFromCurrent(Model $model, Revision $revision): array
    {
        return [
            'title' => $this->generateDiff($revision->title, $model->title ?? ''),
            'content' => $this->generateDiff($revision->content ?? '', $model->content ?? ''),
            'excerpt' => $this->generateDiff($revision->excerpt ?? '', $model->getRawOriginal('excerpt') ?? ''),
        ];
    }

    public function generateDiff(string $old, string $new): string
    {
        if ($old === $new) {
            return '';
        }

        return DiffHelper::calculate(
            $old,
            $new,
            'SideBySide',
            [
                'context' => Differ::CONTEXT_ALL,
                'ignoreCase' => false,
                'ignoreWhitespace' => false,
            ],
            [
                'detailLevel' => 'word',
                'language' => 'eng',
                'lineNumbers' => false,
                'showHeader' => false,
                'wrapperClasses' => ['revision-diff'],
            ]
        );
    }

    public function restore(Model $model, Revision $revision): Model
    {
        return $model->restoreToRevision($revision);
    }

    public function protectRevision(Revision $revision): bool
    {
        return $revision->update(['is_protected' => true]);
    }

    public function unprotectRevision(Revision $revision): bool
    {
        return $revision->update(['is_protected' => false]);
    }

    public function deleteRevision(Revision $revision): bool
    {
        if ($revision->isProtected()) {
            return false;
        }

        return $revision->delete();
    }

    public function cleanupOldRevisions(Model $model, int $keepCount = 50): int
    {
        $revisions = $model->revisions()
            ->where('is_protected', false)
            ->where('is_autosave', false)
            ->orderByDesc('revision_number')
            ->skip($keepCount)
            ->take(PHP_INT_MAX)
            ->get();

        $deleted = 0;
        foreach ($revisions as $revision) {
            if ($revision->delete()) {
                $deleted++;
            }
        }

        return $deleted;
    }

    public function cleanupAutosaves(Model $model, int $keepCount = 5): int
    {
        $autosaves = $model->revisions()
            ->where('is_autosave', true)
            ->orderByDesc('created_at')
            ->skip($keepCount)
            ->take(PHP_INT_MAX)
            ->get();

        $deleted = 0;
        foreach ($autosaves as $autosave) {
            if ($autosave->delete()) {
                $deleted++;
            }
        }

        return $deleted;
    }
}
