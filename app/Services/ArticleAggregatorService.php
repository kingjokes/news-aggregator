<?php

namespace App\Services;

use App\Contracts\NewsSourceAdapter;
use App\Models\Article;
use App\Models\Category;
use App\Models\Source;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ArticleAggregatorService
{
    private array $adapters;

    public function __construct(array $adapters = [])
    {
        $this->adapters = $adapters;
    }

    public function registerAdapter(NewsSourceAdapter $adapter): void
    {
        $this->adapters[] = $adapter;
    }

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

    private function storeArticles(array $articles): int
    {
        $stored = 0;

        foreach ($articles as $articleData) {
            try {
                DB::beginTransaction();

                if (empty($articleData['url'])) {
                    Log::warning('Article missing URL, skipping', ['data' => $articleData]);
                    DB::rollBack();
                    continue;
                }

                $source = $this->getOrCreateSource($articleData['source_name']);
                $category = $this->getOrCreateCategory($articleData['category']);

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

    private function getOrCreateSource(string $name): Source
    {
        return Source::firstOrCreate(
            ['slug' => Str::slug($name)],
            ['name' => $name]
        );
    }

    private function getOrCreateCategory(string $name): Category
    {
        return Category::firstOrCreate(
            ['slug' => Str::slug($name)],
            ['name' => ucfirst($name)]
        );
    }
}