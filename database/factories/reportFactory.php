<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Post;
use App\Models\Comment;
use App\Models\Reply;
use App\Models\User;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\report>
 */
class reportFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
         $reportable = $this->faker->randomElement(
            [
                Post::inRandomOrder()->first(),
                Comment::inRandomOrder()->first(),
                Reply::inRandomOrder()->first(),
            ]
        );

        return [
            "user_id" =>$reportable->user_id ?? User::factory(),
            "reportable_id" => $reportable->id ?? Post::factory(),
            "reportable_type" => $reportable ? get_class( $reportable): Post::class,
            "reason" => $this->faker->sentence(),
            "resolved" => $this->faker->boolean(),
        ];
    }
}
