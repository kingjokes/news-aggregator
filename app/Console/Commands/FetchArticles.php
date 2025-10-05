<?php

namespace App\Console\Commands;

use App\Services\ArticleAggregatorService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;


/**
 * Artisan console command to fetch and aggregate articles from news sources.
 * Executes the aggregator service and displays/logs results.
 */
class FetchArticles extends Command
{
    protected $signature = 'articles:fetch {--limit=100 : Number of articles to fetch per source}';
    protected $description = 'Fetch articles from all configured news sources';


    //Handles command execution: runs aggregation and reports stats.
    public function handle(ArticleAggregatorService $aggregator): int
    {
        try {


            $this->info('Starting article pulling...');
            $this->info('Time: ' . now()->toDateTimeString());


            //Execute aggregation
            $stats = $aggregator->aggregateArticles();


            //Output summary stats
            $this->newLine();
            $this->info("Fetched: {$stats['total_fetched']} articles");
            $this->info("Stored: {$stats['total_stored']} articles");

            //Output errors if present
            if (!empty($stats['errors'])) {
                $this->newLine();
                $this->error('Errors encountered:');
                foreach ($stats['errors'] as $error) {
                    $this->error('  - ' . $error);
                }
            }

            //Final message and logging
            $this->newLine();
            $this->info('Article fetching completed!');

            Log::channel('news_aggregator')->info('Article fetch completed', [
                'fetched' => $stats['total_fetched'],
                'stored' => $stats['total_stored'],
                'errors_count' => count($stats['errors'])
            ]);

            return self::SUCCESS;

        } catch (\Exception $e) {
            // Handle any exceptions: output error, log with trace, return failure
            $this->error('Failed to fetch articles: ' . $e->getMessage());
            Log::error('Article fetch command failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return self::FAILURE;
        }
    }
}