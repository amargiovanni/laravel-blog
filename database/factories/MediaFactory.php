<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Media;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Media>
 */
class MediaFactory extends Factory
{
    protected $model = Media::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $filename = fake()->slug(3).'.jpg';

        return [
            'name' => $filename,
            'path' => 'media/'.date('Y/m').'/'.$filename,
            'disk' => 'public',
            'mime_type' => 'image/jpeg',
            'size' => fake()->numberBetween(10000, 500000),
            'alt' => fake()->sentence(3),
            'title' => fake()->sentence(4),
            'caption' => fake()->optional()->sentence(),
            'sizes' => [
                'thumbnail' => 'media/'.date('Y/m').'/'.fake()->slug(3).'_thumbnail.jpg',
                'medium' => 'media/'.date('Y/m').'/'.fake()->slug(3).'_medium.jpg',
                'large' => 'media/'.date('Y/m').'/'.fake()->slug(3).'_large.jpg',
            ],
            'uploaded_by' => User::factory(),
        ];
    }

    /**
     * Media is a document (PDF).
     */
    public function document(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => fake()->slug(3).'.pdf',
            'path' => 'media/'.date('Y/m').'/'.fake()->slug(3).'.pdf',
            'mime_type' => 'application/pdf',
            'sizes' => null,
        ]);
    }

    /**
     * Media is a PNG image.
     */
    public function png(): static
    {
        $filename = fake()->slug(3).'.png';

        return $this->state(fn (array $attributes) => [
            'name' => $filename,
            'path' => 'media/'.date('Y/m').'/'.$filename,
            'mime_type' => 'image/png',
        ]);
    }
}
