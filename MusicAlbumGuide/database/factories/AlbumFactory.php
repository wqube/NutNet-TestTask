<?php

namespace Database\Factories;

use App\Models\Album;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Album>
 */
class AlbumFactory extends Factory
{
    protected $model = Album::class;

    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'artist' => fake()->name(),
            'description' => fake()->paragraph(),
            'cover_url' => fake()->imageUrl(640, 640, 'music', true),
            'user_id' => User::factory(),
        ];
    }
}
