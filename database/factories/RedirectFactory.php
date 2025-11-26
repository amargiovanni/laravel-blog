<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Redirect;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Redirect>
 */
class RedirectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'source_url' => '/'.fake()->unique()->slug(3),
            'target_url' => '/'.fake()->slug(2),
            'status_code' => 301,
            'is_active' => true,
            'is_automatic' => false,
            'hits' => 0,
            'last_hit_at' => null,
        ];
    }

    /**
     * Indicate that the redirect is a 302 temporary redirect.
     */
    public function temporary(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status_code' => 302,
        ]);
    }

    /**
     * Indicate that the redirect is a 301 permanent redirect.
     */
    public function permanent(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status_code' => 301,
        ]);
    }

    /**
     * Indicate that the redirect is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the redirect was automatically created.
     */
    public function automatic(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_automatic' => true,
        ]);
    }

    /**
     * Indicate that the redirect has some hits.
     */
    public function withHits(int $count = 10): static
    {
        return $this->state(fn (array $attributes): array => [
            'hits' => $count,
            'last_hit_at' => now()->subDays(fake()->numberBetween(1, 30)),
        ]);
    }
}
