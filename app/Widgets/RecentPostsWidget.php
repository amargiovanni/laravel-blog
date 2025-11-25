<?php

declare(strict_types=1);

namespace App\Widgets;

use App\Models\Post;
use Illuminate\Contracts\View\View;

class RecentPostsWidget extends BaseWidget
{
    public static function getName(): string
    {
        return 'Recent Posts';
    }

    public static function getDescription(): string
    {
        return 'Shows a list of recent blog posts';
    }

    public static function getDefaultSettings(): array
    {
        return [
            'count' => 5,
            'show_date' => true,
            'show_thumbnail' => false,
        ];
    }

    public static function getSettingsFields(): array
    {
        return [
            [
                'type' => 'select',
                'name' => 'count',
                'label' => 'Number of Posts',
                'options' => [
                    3 => '3 posts',
                    5 => '5 posts',
                    10 => '10 posts',
                    15 => '15 posts',
                ],
            ],
            [
                'type' => 'toggle',
                'name' => 'show_date',
                'label' => 'Show Post Date',
            ],
            [
                'type' => 'toggle',
                'name' => 'show_thumbnail',
                'label' => 'Show Thumbnail',
            ],
        ];
    }

    public function render(): View
    {
        $count = (int) $this->getSetting('count');
        $showDate = (bool) $this->getSetting('show_date');
        $showThumbnail = (bool) $this->getSetting('show_thumbnail');

        $posts = Post::query()
            ->published()
            ->latest('published_at')
            ->limit($count)
            ->get();

        return view('widgets.recent-posts', [
            'title' => $this->getTitle(),
            'posts' => $posts,
            'showDate' => $showDate,
            'showThumbnail' => $showThumbnail,
        ]);
    }
}
