<?php

namespace Database\Seeders;

use App\Enums\ArticleStatus;
use App\Models\Article;
use Illuminate\Database\Seeder;

class ArticleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Article::factory(20)->create();
        Article::factory(10)->create([
            'status' => ArticleStatus::PUBLISHED,
        ]);
    }
}
