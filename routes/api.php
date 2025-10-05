<?php

use App\Http\Controllers\Api\ArticleController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\HealthCheckController;
use App\Http\Controllers\Api\SourceController;
use Illuminate\Support\Facades\Route;

Route::get('health', [HealthCheckController::class, 'index']);

Route::prefix('v1')->group(function () {
    
    Route::prefix('articles')->group(function () {
        Route::get('/', [ArticleController::class, 'index']);
        Route::get('/search', [ArticleController::class, 'search']);
        Route::get('/recent', [ArticleController::class, 'recent']);
        Route::get('/authors', [ArticleController::class, 'authors']);
        Route::get('/source/{slug}', [ArticleController::class, 'bySource']);
        Route::get('/category/{slug}', [ArticleController::class, 'byCategory']);
        Route::get('/{article}', [ArticleController::class, 'show']);
    });
    
    Route::prefix('sources')->group(function () {
        Route::get('/', [SourceController::class, 'index']);
        Route::get('/{source}', [SourceController::class, 'show']);
    });
    
    Route::prefix('categories')->group(function () {
        Route::get('/', [CategoryController::class, 'index']);
        Route::get('/{category}', [CategoryController::class, 'show']);
    });
});