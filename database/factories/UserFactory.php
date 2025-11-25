<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    protected static ?string $password = null;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'avatar' => null,
            'bio' => fake()->optional(0.7)->paragraph(),
            'theme_preference' => fake()->randomElement(['light', 'dark', 'system']),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes): array => [
            'email_verified_at' => null,
        ]);
    }

    public function admin(): static
    {
        return $this->afterCreating(function ($user): void {
            $user->assignRole('admin');
        });
    }

    public function editor(): static
    {
        return $this->afterCreating(function ($user): void {
            $user->assignRole('editor');
        });
    }

    public function author(): static
    {
        return $this->afterCreating(function ($user): void {
            $user->assignRole('author');
        });
    }

    public function subscriber(): static
    {
        return $this->afterCreating(function ($user): void {
            $user->assignRole('subscriber');
        });
    }
}
