<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Comment;
use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Comment>
 */
class CommentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'post_id' => Post::factory(),
            'parent_id' => null,
            'user_id' => null,
            'author_name' => fake()->name(),
            'author_email' => fake()->safeEmail(),
            'author_url' => fake()->optional(0.3)->url(),
            'content' => fake()->paragraph(3),
            'status' => Comment::STATUS_PENDING,
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
            'is_notify_replies' => false,
            'approved_at' => null,
        ];
    }

    /**
     * Indicate that the comment is approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => Comment::STATUS_APPROVED,
            'approved_at' => now(),
        ]);
    }

    /**
     * Indicate that the comment is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => Comment::STATUS_PENDING,
            'approved_at' => null,
        ]);
    }

    /**
     * Indicate that the comment is rejected.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => Comment::STATUS_REJECTED,
            'approved_at' => null,
        ]);
    }

    /**
     * Indicate that the comment is marked as spam.
     */
    public function spam(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => Comment::STATUS_SPAM,
            'approved_at' => null,
        ]);
    }

    /**
     * Indicate that the comment is a reply to another comment.
     */
    public function replyTo(Comment $parent): static
    {
        return $this->state(fn (array $attributes): array => [
            'post_id' => $parent->post_id,
            'parent_id' => $parent->id,
        ]);
    }

    /**
     * Indicate that the commenter wants reply notifications.
     */
    public function withNotifications(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_notify_replies' => true,
        ]);
    }

    /**
     * Create a comment with content that contains links.
     */
    public function withLinks(): static
    {
        return $this->state(fn (array $attributes): array => [
            'content' => fake()->paragraph().' Check out https://example.com for more info.',
        ]);
    }
}
