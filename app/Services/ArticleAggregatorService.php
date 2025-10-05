<?php

namespace App\Services;

use App\Contracts\NewsSourceAdapter;
use App\Models\Article;
use App\Models\Category;
use App\Models\Source;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Service for aggregating articles from registered news source adapters.
 * Fetches, transforms, and stores articles in the database with source/category management.
 */
class ArticleAggregatorService
{
    private array $adapters;

    public function __construct(array $adapters = [])
    {
        $this->adapters = $adapters;
    }


    /**
     * Registers a new news source adapter.
     *
     * @param NewsSourceAdapter $adapter
     * @return void
     */
    public function registerAdapter(NewsSourceAdapter $adapter): void
    {
        $this->adapters[] = $adapter;
    }



    /**
     * Aggregates articles from all registered adapters.
     * Returns aggregation statistics.
     * @return array Stats: total_fetched, total_stored, errors
     */
    public function aggregateArticles(): array
    {
        $stats = [
            'total_fetched' => 0,
            'total_stored' => 0,
            'errors' => [],
        ];

        if (empty($this->adapters)) {
            $error = 'No adapters registered for article aggregation';
            Log::warning($error);
            $stats['errors'][] = $error;
            return $stats;
        }

        //Process each adapter
        foreach ($this->adapters as $adapter) {
            try {
                Log::info("Starting fetch from {$adapter->getSourceName()}");

                $articles = $adapter->fetchArticles();
                $stats['total_fetched'] += count($articles);

                if (empty($articles)) {
                    Log::warning("No articles fetched from {$adapter->getSourceName()}");
                    continue;
                }

                $stored = $this->storeArticles($articles);
                $stats['total_stored'] += $stored;

                Log::info("Stored {$stored} articles from {$adapter->getSourceName()}");

            } catch (\Exception $e) {
                $error = "Error with {$adapter->getSourceName()}: {$e->getMessage()}";
                $stats['errors'][] = $error;
                Log::error($error, [
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }

        return $stats;
    }



    /**
     * Stores fetched articles in the database.
     * @param array $articles Raw article data array
     * @return int Number stored
     */
    private function storeArticles(array $articles): int
    {
        $stored = 0;

        foreach ($articles as $articleData) {
            try {
                DB::beginTransaction();

                //Validate URL
                if (empty($articleData['url'])) {
                    Log::warning('Article missing URL, skipping', ['data' => $articleData]);
                    DB::rollBack();
                    continue;
                }

                // Resolve source and category
                $source = $this->getOrCreateSource($articleData['source_name']);
                $category = $this->getOrCreateCategory($articleData['category']);

                //Update or create article
                $article = Article::updateOrCreate(
                    ['external_id' => $articleData['external_id']],
                    [
                        'title' => $articleData['title'],
                        'description' => $articleData['description'],
                        'content' => $articleData['content'],
                        'author' => $articleData['author'],
                        'source_id' => $source->id,
                        'category_id' => $category->id,
                        'url' => $articleData['url'],
                        'image_url' => $articleData['image_url'],
                        'published_at' => $articleData['published_at'],
                    ]
                );

                $stored++;
                DB::commit();

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error("Failed to store article: {$e->getMessage()}", [
                    'article_data' => $articleData,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $stored;
    }


    /**
     * Retrieves or creates a Source model by name (uses slug for uniqueness).
     *
     * @param string $name Source name
     * @return Source
     */
    private function getOrCreateSource(string $name): Source
    {
        return Source::firstOrCreate(
            ['slug' => Str::slug($name)],
            ['name' => $name]
        );
    }


    /**
     * Retrieves or creates a Category model by name (uses slug for uniqueness).
     *
     * @param string $name Category name
     * @return Category
     */
    private function getOrCreateCategory(string $name): Category
    {
        return Category::firstOrCreate(
            ['slug' => Str::slug($name)],
            ['name' => ucfirst($name)]
        );
    }
}