<?php

namespace App\Jobs;

use App\Services\ArticleAggregatorService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class FetchArticlesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 300;

    public function handle(ArticleAggregatorService $aggregator): void
    {
        Log::channel('news_aggregator')->info('Starting background article fetch');

        $stats = $aggregator->aggregateArticles();

        Log::channel('news_aggregator')->info('Article fetch completed', [
            'fetched' => $stats['total_fetched'],
            'stored' => $stats['total_stored'],
            'errors' => count($stats['errors'])
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        Log::channel('news_aggregator')->error('Article fetch job failed', [
            'error' => $exception->getMessage()
        ]);
    }
}