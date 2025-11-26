<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Subscriber;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Subscriber>
 */
class SubscriberFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'email' => fake()->unique()->safeEmail(),
            'name' => fake()->optional(0.7)->name(),
            'unsubscribe_token' => Str::random(64),
            'verified_at' => null,
            'unsubscribed_at' => null,
            'subscribed_ip' => fake()->ipv4(),
        ];
    }

    /**
     * Indicate that the subscriber is verified.
     */
    public function verified(): static
    {
        return $this->state(fn (array $attributes) => [
            'verified_at' => now()->subDays(rand(1, 30)),
        ]);
    }

    /**
     * Indicate that the subscriber is unsubscribed.
     */
    public function unsubscribed(): static
    {
        return $this->state(fn (array $attributes) => [
            'verified_at' => now()->subDays(rand(30, 60)),
            'unsubscribed_at' => now()->subDays(rand(1, 10)),
        ]);
    }

    /**
     * Indicate that the subscriber is active (verified and not unsubscribed).
     */
    public function active(): static
    {
        return $this->verified();
    }
}
