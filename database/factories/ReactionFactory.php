<?php

namespace Database\Factories;

use App\Enums\ReactionType;
use App\Models\Article;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Reaction>
 */
class ReactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $reactionable = $this->reactionable();
        $userIds = User::pluck('id')->toArray();

        return [
            'user_id' => fake()->randomElement($userIds),
            'type' => fake()->randomElement([ReactionType::LIKE->value, ReactionType::DISLIKE->value]),
            'reactionable_id' => $reactionable->id,
            'reactionable_type' => get_class($reactionable),
        ];
    }

    public function reactionable()
    {
        return $this->faker->randomElement([
            Article::inRandomOrder()->first(),
            Comment::inRandomOrder()->first(),
        ]);
    }
}
