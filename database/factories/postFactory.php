<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Post>
 */
class postFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
    $images = [
            'posts/image1.jpeg',
            'posts/image2.jpeg',
            'posts/image3.jpeg',
        ];
        return [
            'title' => fake()->sentence(),
            'content' => fake()->paragraph(),
            'image' => fake()->randomElement($images),
            'user_id' => User::factory(),
        ];
    }
}
