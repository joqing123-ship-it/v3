<?php

namespace Database\Factories;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Post;
use App\Models\Comment;
use App\Models\Reply;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Like>
 */
class likeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {

        $likeable = $this->faker->randomElement(
            [
                Post::inRandomOrder()->first(),
                Comment::inRandomOrder()->first(),
                Reply::inRandomOrder()->first(),
            ]
        );

        return [
            "user_id" =>$likeable->user_id ?? User::factory(),
            "likeable_id" => $likeable->id ?? Post::factory(),
            "likeable_type" => $likeable ? get_class($likeable): Post::class,
        ];
    }
}
