<?php

declare(strict_types=1);

namespace App\Widgets;

use Illuminate\Contracts\View\View;

class SearchWidget extends BaseWidget
{
    public static function getName(): string
    {
        return 'Search';
    }

    public static function getDescription(): string
    {
        return 'A search form for finding content';
    }

    public static function getDefaultSettings(): array
    {
        return [
            'placeholder' => 'Search...',
        ];
    }

    public static function getSettingsFields(): array
    {
        return [
            [
                'type' => 'text',
                'name' => 'placeholder',
                'label' => 'Placeholder Text',
            ],
        ];
    }

    public function render(): View
    {
        return view('widgets.search', [
            'title' => $this->getTitle(),
            'placeholder' => $this->getSetting('placeholder'),
        ]);
    }
}
