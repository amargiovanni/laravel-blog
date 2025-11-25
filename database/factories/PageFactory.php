<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Page;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Page>
 */
class PageFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(),
            'slug' => null, // Will be auto-generated
            'content' => fake()->paragraphs(5, true),
            'excerpt' => fake()->optional(0.7)->paragraph(),
            'parent_id' => null,
            'author_id' => User::factory(),
            'status' => 'draft',
            'template' => 'default',
            'published_at' => null,
            'featured_image_id' => null,
            'meta_title' => fake()->optional(0.5)->sentence(),
            'meta_description' => fake()->optional(0.5)->text(160),
            'focus_keyword' => fake()->optional(0.3)->word(),
            'sort_order' => 0,
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => 'draft',
            'published_at' => null,
        ]);
    }

    public function scheduled(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => 'scheduled',
            'published_at' => fake()->dateTimeBetween('+1 day', '+1 month'),
        ]);
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => 'published',
            'published_at' => fake()->dateTimeBetween('-1 month', 'now'),
        ]);
    }

    public function withParent(Page $parent): static
    {
        return $this->state(fn (array $attributes): array => [
            'parent_id' => $parent->id,
        ]);
    }

    public function withTemplate(string $template): static
    {
        return $this->state(fn (array $attributes): array => [
            'template' => $template,
        ]);
    }

    public function withSortOrder(int $order): static
    {
        return $this->state(fn (array $attributes): array => [
            'sort_order' => $order,
        ]);
    }
}
