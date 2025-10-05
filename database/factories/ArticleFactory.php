<?php

namespace Database\Factories;

use App\Models\Article;
use App\Models\Category;
use App\Models\Source;
use Illuminate\Database\Eloquent\Factories\Factory;

class ArticleFactory extends Factory
{
    protected $model = Article::class;

    public function definition(): array
    {
        return [
            'title' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'content' => fake()->paragraphs(5, true),
            'author' => fake()->name(),
            'source_id' => Source::factory(),
            'category_id' => Category::factory(),
            'url' => fake()->unique()->url(),
            'image_url' => fake()->imageUrl(640, 480, 'news', true),
            'published_at' => fake()->dateTimeBetween('-1 month', 'now'),
            'external_id' => fake()->unique()->uuid(),
        ];
    }
}