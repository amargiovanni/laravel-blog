<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\WidgetInstance;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WidgetInstance>
 */
class WidgetInstanceFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'area' => 'primary_sidebar',
            'widget_type' => 'search',
            'title' => null,
            'settings' => [],
            'sort_order' => 0,
        ];
    }

    public function forArea(string $area): static
    {
        return $this->state(fn () => ['area' => $area]);
    }

    public function ofType(string $type): static
    {
        return $this->state(fn () => ['widget_type' => $type]);
    }

    public function withTitle(string $title): static
    {
        return $this->state(fn () => ['title' => $title]);
    }

    public function withSettings(array $settings): static
    {
        return $this->state(fn () => ['settings' => $settings]);
    }

    public function search(): static
    {
        return $this->ofType('search');
    }

    public function recentPosts(): static
    {
        return $this->ofType('recent_posts')->withSettings(['count' => 5, 'show_date' => true]);
    }

    public function categories(): static
    {
        return $this->ofType('categories')->withSettings(['show_count' => true]);
    }

    public function tags(): static
    {
        return $this->ofType('tags')->withSettings(['max_tags' => 30]);
    }

    public function archives(): static
    {
        return $this->ofType('archives')->withSettings(['type' => 'monthly', 'limit' => 12]);
    }

    public function customHtml(string $content = ''): static
    {
        return $this->ofType('custom_html')->withSettings(['content' => $content]);
    }
}
