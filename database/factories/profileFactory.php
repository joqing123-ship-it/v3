<?php

namespace Database\Factories;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Profile>
 */
class profileFactory extends Factory
{
    /**
     * Define the model's default state.

     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $images = [
            'profiles/image1.jpeg',
            'profiles/image2.jpeg',
            'profiles/image3.jpeg',
        ];
        return [
            'name' => fake()->name(),
            'profile_image' => fake()->randomElement($images),
            'description' => fake()->sentence(),
            'phone' => fake()->phoneNumber(),
            'user_id' => User::factory(),
        ];
    }
}
