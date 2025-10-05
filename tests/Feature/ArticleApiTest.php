<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Category;
use App\Models\Source;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArticleApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_articles(): void
    {
        $source = Source::factory()->create();
        $category = Category::factory()->create();
        Article::factory()->count(5)->create([
            'source_id' => $source->id,
            'category_id' => $category->id,
        ]);

        $response = $this->getJson('/api/v1/articles');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'title', 'description', 'source', 'category']
                ]
            ]);
    }

    public function test_can_filter_articles_by_date(): void
    {
        $source = Source::factory()->create();
        Article::factory()->create([
            'source_id' => $source->id,
            'published_at' => now()->subDays(5),
        ]);

        $response = $this->getJson('/api/v1/articles?from=' . now()->subDays(3)->toDateString());

        $response->assertStatus(200);
    }

    public function test_can_filter_articles_by_source(): void
    {
        $source = Source::factory()->create();
        Article::factory()->count(3)->create(['source_id' => $source->id]);

        $response = $this->getJson("/api/v1/articles?source={$source->id}");

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_can_search_articles(): void
    {
        $source = Source::factory()->create();
        Article::factory()->create([
            'source_id' => $source->id,
            'title' => 'Laravel Framework News',
        ]);

        $response = $this->getJson('/api/v1/articles?search=Laravel');

        $response->assertStatus(200);
    }

    public function test_can_show_single_article(): void
    {
        $source = Source::factory()->create();
        $article = Article::factory()->create(['source_id' => $source->id]);

        $response = $this->getJson("/api/v1/articles/{$article->id}");

        $response->assertStatus(200)
            ->assertJson([
                'id' => $article->id,
                'title' => $article->title,
            ]);
    }
}