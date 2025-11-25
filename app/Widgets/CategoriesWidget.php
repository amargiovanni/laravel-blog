<?php

declare(strict_types=1);

namespace App\Widgets;

use App\Models\Category;
use Illuminate\Contracts\View\View;

class CategoriesWidget extends BaseWidget
{
    public static function getName(): string
    {
        return 'Categories';
    }

    public static function getDescription(): string
    {
        return 'Shows a list of categories with post counts';
    }

    public static function getDefaultSettings(): array
    {
        return [
            'show_count' => true,
            'hierarchical' => false,
        ];
    }

    public static function getSettingsFields(): array
    {
        return [
            [
                'type' => 'toggle',
                'name' => 'show_count',
                'label' => 'Show Post Count',
            ],
            [
                'type' => 'toggle',
                'name' => 'hierarchical',
                'label' => 'Show Hierarchy',
            ],
        ];
    }

    public function render(): View
    {
        $showCount = (bool) $this->getSetting('show_count');
        $hierarchical = (bool) $this->getSetting('hierarchical');

        $query = Category::query()->withCount('posts');

        if ($hierarchical) {
            $categories = $query->whereNull('parent_id')
                ->with(['children' => fn ($q) => $q->withCount('posts')])
                ->get();
        } else {
            $categories = $query->orderBy('name')->get();
        }

        return view('widgets.categories', [
            'title' => $this->getTitle(),
            'categories' => $categories,
            'showCount' => $showCount,
            'hierarchical' => $hierarchical,
        ]);
    }
}
