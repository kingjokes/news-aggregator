<?php

namespace App\Providers;

use App\Services\Adapters\GuardianAdapter;
use App\Services\Adapters\NewsApiAdapter;
use App\Services\Adapters\NewYorkTimesAdapter;
use App\Services\ArticleAggregatorService;
use Illuminate\Support\ServiceProvider;

class NewsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ArticleAggregatorService::class, function ($app) {
            $service = new ArticleAggregatorService();
            
            $service->registerAdapter(new NewsApiAdapter());
            $service->registerAdapter(new GuardianAdapter());
            $service->registerAdapter(new NewYorkTimesAdapter());
            
            return $service;
        });
    }

    public function boot(): void
    {
        //
    }
}