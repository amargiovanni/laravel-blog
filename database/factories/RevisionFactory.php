<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Post;
use App\Models\Revision;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Revision>
 */
class RevisionFactory extends Factory
{
    protected $model = Revision::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'revisionable_type' => Post::class,
            'revisionable_id' => Post::factory(),
            'user_id' => User::factory(),
            'revision_number' => 1,
            'title' => fake()->sentence(),
            'content' => fake()->paragraphs(3, true),
            'excerpt' => fake()->paragraph(),
            'metadata' => [
                'status' => 'draft',
                'slug' => fake()->slug(),
            ],
            'is_autosave' => false,
            'is_protected' => false,
            'created_at' => now(),
        ];
    }

    public function forPost(Post $post): static
    {
        return $this->state(fn (array $attributes) => [
            'revisionable_type' => Post::class,
            'revisionable_id' => $post->id,
            'title' => $post->title,
            'content' => $post->content,
            'excerpt' => $post->getRawOriginal('excerpt'),
            'metadata' => [
                'status' => $post->status,
                'slug' => $post->slug,
                'featured_image_id' => $post->featured_image_id,
            ],
        ]);
    }

    public function autosave(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_autosave' => true,
        ]);
    }

    public function protected(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_protected' => true,
        ]);
    }

    public function revisionNumber(int $number): static
    {
        return $this->state(fn (array $attributes) => [
            'revision_number' => $number,
        ]);
    }

    public function byUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }
}
