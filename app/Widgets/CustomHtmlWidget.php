<?php

declare(strict_types=1);

namespace App\Widgets;

use Illuminate\Contracts\View\View;

class CustomHtmlWidget extends BaseWidget
{
    public static function getName(): string
    {
        return 'Custom HTML';
    }

    public static function getDescription(): string
    {
        return 'Custom HTML or text content';
    }

    public static function getDefaultSettings(): array
    {
        return [
            'content' => '',
        ];
    }

    public static function getSettingsFields(): array
    {
        return [
            [
                'type' => 'textarea',
                'name' => 'content',
                'label' => 'HTML Content',
                'rows' => 6,
            ],
        ];
    }

    public function render(): View
    {
        $content = $this->getSetting('content');
        $sanitizedContent = $this->sanitizeHtml($content);

        return view('widgets.custom-html', [
            'title' => $this->getTitle(),
            'content' => $sanitizedContent,
        ]);
    }

    protected function sanitizeHtml(?string $html): string
    {
        if (empty($html)) {
            return '';
        }

        // Remove potentially dangerous tags
        $dangerous = [
            'script', 'iframe', 'object', 'embed', 'form',
            'input', 'button', 'select', 'textarea',
        ];

        foreach ($dangerous as $tag) {
            $html = preg_replace('/<'.$tag.'\b[^>]*>.*?<\/'.$tag.'>/is', '', $html);
            $html = preg_replace('/<'.$tag.'\b[^>]*\/?>/is', '', $html);
        }

        // Remove event handlers
        $html = preg_replace('/\s*on\w+\s*=\s*["\'][^"\']*["\']/i', '', $html);
        $html = preg_replace('/\s*on\w+\s*=\s*[^\s>]*/i', '', $html);

        // Remove javascript: protocol
        $html = preg_replace('/javascript\s*:/i', '', $html);

        return $html;
    }
}
