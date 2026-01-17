<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Plant>
 */
class plantFactory extends Factory
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
            "name" => fake()->word(),
            'image' => fake()->randomElement($images),
            'user_id' => User::factory(),
        ];
    }
}
