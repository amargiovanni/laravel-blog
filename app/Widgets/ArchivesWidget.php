<?php

declare(strict_types=1);

namespace App\Widgets;

use App\Models\Post;
use Illuminate\Contracts\View\View;

class ArchivesWidget extends BaseWidget
{
    public static function getName(): string
    {
        return 'Archives';
    }

    public static function getDescription(): string
    {
        return 'Shows monthly or yearly archive links';
    }

    public static function getDefaultSettings(): array
    {
        return [
            'type' => 'monthly',
            'show_count' => true,
            'limit' => 12,
        ];
    }

    public static function getSettingsFields(): array
    {
        return [
            [
                'type' => 'select',
                'name' => 'type',
                'label' => 'Archive Type',
                'options' => [
                    'monthly' => 'Monthly',
                    'yearly' => 'Yearly',
                ],
            ],
            [
                'type' => 'toggle',
                'name' => 'show_count',
                'label' => 'Show Post Count',
            ],
            [
                'type' => 'number',
                'name' => 'limit',
                'label' => 'Maximum Archives',
                'min' => 3,
                'max' => 36,
            ],
        ];
    }

    public function render(): View
    {
        $type = $this->getSetting('type');
        $showCount = (bool) $this->getSetting('show_count');
        $limit = (int) $this->getSetting('limit');

        $archives = $this->getArchives($type, $limit);

        return view('widgets.archives', [
            'title' => $this->getTitle(),
            'archives' => $archives,
            'type' => $type,
            'showCount' => $showCount,
        ]);
    }

    protected function getArchives(string $type, int $limit): array
    {
        $dateFormat = $type === 'yearly' ? '%Y' : '%Y-%m';
        $dateFormatLabel = $type === 'yearly' ? '%Y' : '%M %Y';

        $results = Post::query()
            ->published()
            ->selectRaw("strftime('{$dateFormat}', published_at) as period")
            ->selectRaw('COUNT(*) as count')
            ->groupBy('period')
            ->orderByDesc('period')
            ->limit($limit)
            ->get();

        return $results->map(function ($row) use ($type) {
            $date = $type === 'yearly'
                ? \Carbon\Carbon::createFromFormat('Y', $row->period)
                : \Carbon\Carbon::createFromFormat('Y-m', $row->period);

            return [
                'period' => $row->period,
                'label' => $type === 'yearly' ? $date->format('Y') : $date->format('F Y'),
                'count' => $row->count,
                'url' => route('archives', ['period' => $row->period]),
            ];
        })->toArray();
    }
}
