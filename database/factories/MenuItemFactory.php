<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MenuItem>
 */
class MenuItemFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'menu_id' => Menu::factory(),
            'parent_id' => null,
            'label' => fake()->words(2, true),
            'linkable_type' => null,
            'linkable_id' => null,
            'url' => null,
            'target' => '_self',
            'css_class' => null,
            'title_attribute' => null,
            'sort_order' => 0,
        ];
    }

    public function customLink(string $url, string $label): static
    {
        return $this->state(fn () => [
            'url' => $url,
            'label' => $label,
        ]);
    }

    public function openInNewTab(): static
    {
        return $this->state(fn () => ['target' => '_blank']);
    }

    public function withCssClass(string $class): static
    {
        return $this->state(fn () => ['css_class' => $class]);
    }

    public function forMenu(Menu $menu): static
    {
        return $this->state(fn () => ['menu_id' => $menu->id]);
    }

    public function asChild(MenuItem $parent): static
    {
        return $this->state(fn () => [
            'parent_id' => $parent->id,
            'menu_id' => $parent->menu_id,
        ]);
    }
}
