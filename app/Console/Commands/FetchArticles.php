<?php

namespace App\Console\Commands;

use App\Services\ArticleAggregatorService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class FetchArticles extends Command
{
    protected $signature = 'articles:fetch {--limit=200 : Number of articles to fetch per source}';
    protected $description = 'Fetch articles from all configured news sources';

    public function handle(ArticleAggregatorService $aggregator): int
    {
        try {
            $this->info('Starting article pulling...');
            $this->info('Time: ' . now()->toDateTimeString());

            $stats = $aggregator->aggregateArticles();

            $this->newLine();
            $this->info("Fetched: {$stats['total_fetched']} articles");
            $this->info("Stored: {$stats['total_stored']} articles");

            if (!empty($stats['errors'])) {
                $this->newLine();
                $this->error('⨯ Errors encountered:');
                foreach ($stats['errors'] as $error) {
                    $this->error('  - ' . $error);
                }
            }

            $this->newLine();
            $this->info('Article fetching completed!');

            Log::channel('news_aggregator')->info('Article fetch completed', [
                'fetched' => $stats['total_fetched'],
                'stored' => $stats['total_stored'],
                'errors_count' => count($stats['errors'])
            ]);

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Failed to fetch articles: ' . $e->getMessage());
            Log::error('Article fetch command failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return self::FAILURE;
        }
    }
}