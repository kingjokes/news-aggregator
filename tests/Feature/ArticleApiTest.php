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
                'data' => [
                    'id' => $article->id,
                    'title' => $article->title,
                ]
            ]);
    }

    public function test_can_get_authors(): void
    {
        $source = Source::factory()->create();
        Article::factory()->count(3)->create([
            'source_id' => $source->id,
            'author' => 'John Doe'
        ]);

        $response = $this->getJson('/api/v1/articles/authors');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data'
            ]);
    }

    public function test_can_search_with_query_parameter(): void
    {
        $source = Source::factory()->create();
        Article::factory()->create([
            'source_id' => $source->id,
            'title' => 'Testing Laravel Application',
        ]);

        $response = $this->getJson('/api/v1/articles/search?q=Laravel');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'title', 'description']
                ]
            ]);
    }

    public function test_search_validation_fails_without_query(): void
    {
        $response = $this->getJson('/api/v1/articles/search');

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Validation failed'
            ]);
    }

    public function test_can_get_recent_articles(): void
    {
        $source = Source::factory()->create();
        Article::factory()->count(5)->create([
            'source_id' => $source->id,
            'published_at' => now()->subDays(2),
        ]);

        $response = $this->getJson('/api/v1/articles/recent?days=7');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'title', 'published_at']
                ]
            ]);
    }

    public function test_can_filter_by_source_slug(): void
    {
        $source = Source::factory()->create(['slug' => 'test-source']);
        Article::factory()->count(2)->create(['source_id' => $source->id]);

        $response = $this->getJson('/api/v1/articles/source/test-source');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    public function test_can_filter_by_category_slug(): void
    {
        $source = Source::factory()->create();
        $category = Category::factory()->create(['slug' => 'technology']);
        Article::factory()->count(3)->create([
            'source_id' => $source->id,
            'category_id' => $category->id,
        ]);

        $response = $this->getJson('/api/v1/articles/category/technology');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }
}