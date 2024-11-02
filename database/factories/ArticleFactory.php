<?php

namespace Database\Factories;

use App\Enums\ArticleStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Article>
 */
class ArticleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $userIds = User::pluck('id')->toArray();

        return [
            'user_id' => fake()->randomElement($userIds),
            'title' => fake()->sentence(),
            'content' => fake()->paragraph(),
            'status' => fake()->randomElement([ArticleStatus::DRAFT->value, ArticleStatus::PENDING->value, ArticleStatus::APPROVED->value, ArticleStatus::REJECTED->value, ArticleStatus::PUBLISHED->value]),
            'submitted_at' => fake()->dateTime(),
            'approved_at' => fake()->dateTime(),
            'rejected_at' => fake()->dateTime(),
            'published_at' => fake()->dateTime(),
        ];
    }
}
