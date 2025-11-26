<?php

declare(strict_types=1);

namespace App\Widgets;

use Illuminate\Contracts\View\View;

class NewsletterWidget extends BaseWidget
{
    public static function getName(): string
    {
        return 'Newsletter';
    }

    public static function getDescription(): string
    {
        return 'Newsletter subscription form';
    }

    public static function getDefaultSettings(): array
    {
        return [
            'description' => 'Subscribe to receive updates on new posts and content.',
            'button_text' => 'Subscribe',
            'show_name_field' => false,
        ];
    }

    public static function getSettingsFields(): array
    {
        return [
            [
                'type' => 'textarea',
                'name' => 'description',
                'label' => 'Description',
            ],
            [
                'type' => 'text',
                'name' => 'button_text',
                'label' => 'Button Text',
            ],
            [
                'type' => 'toggle',
                'name' => 'show_name_field',
                'label' => 'Show Name Field',
            ],
        ];
    }

    public function render(): View
    {
        return view('widgets.newsletter', [
            'title' => $this->getTitle(),
            'description' => $this->getSetting('description'),
            'buttonText' => $this->getSetting('button_text'),
            'showNameField' => (bool) $this->getSetting('show_name_field'),
        ]);
    }
}
