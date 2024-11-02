<?php

namespace Database\Factories;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Comment>
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
        $articleIds = Article::where('status', ArticleStatus::PUBLISHED)->pluck('id')->toArray();
        $userIds = User::pluck('id')->toArray();
        $commentIds = Comment::pluck('id')->toArray();

        return [
            'article_id' => fake()->randomElement($articleIds),
            'user_id' => fake()->randomElement($userIds),
            'parent_id' => fake()->randomElement($commentIds),
            'content' => fake()->sentence(),
        ];
    }
}
