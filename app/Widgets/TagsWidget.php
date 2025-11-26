<?php

declare(strict_types=1);

namespace App\Widgets;

use App\Models\Tag;
use Illuminate\Contracts\View\View;

class TagsWidget extends BaseWidget
{
    public static function getName(): string
    {
        return 'Tag Cloud';
    }

    public static function getDescription(): string
    {
        return 'Shows tags with varying sizes based on usage';
    }

    public static function getDefaultSettings(): array
    {
        return [
            'max_tags' => 30,
        ];
    }

    public static function getSettingsFields(): array
    {
        return [
            [
                'type' => 'number',
                'name' => 'max_tags',
                'label' => 'Maximum Tags',
                'min' => 5,
                'max' => 100,
            ],
        ];
    }

    public function render(): View
    {
        $maxTags = (int) $this->getSetting('max_tags');

        $tags = Tag::query()
            ->withCount('posts')
            ->get()
            ->filter(fn ($tag) => $tag->posts_count > 0)
            ->sortByDesc('posts_count')
            ->take($maxTags);

        // Calculate tag sizes based on post count
        $maxCount = $tags->max('posts_count') ?: 1;
        $minCount = $tags->min('posts_count') ?: 1;

        $tags = $tags->map(function ($tag) use ($maxCount, $minCount) {
            $range = max(1, $maxCount - $minCount);
            $ratio = ($tag->posts_count - $minCount) / $range;
            $tag->size = round(0.75 + ($ratio * 0.5), 2); // Size from 0.75rem to 1.25rem

            return $tag;
        })->shuffle(); // Randomize order for cloud effect

        return view('widgets.tags', [
            'title' => $this->getTitle(),
            'tags' => $tags,
        ]);
    }
}
