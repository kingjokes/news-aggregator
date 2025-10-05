<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

/**
 * Handles system health monitoring
 */
class HealthCheckController extends Controller
{
    /**
     * Check system health status
     * Returns 200 if healthy, 503 if unhealthy
     */
    public function index(): JsonResponse
    {
        // Initialize health status
        $status = [
            'status' => 'healthy',
            'timestamp' => now()->toIso8601String(),
            'checks' => []
        ];

        // Check database connectivity
        try {
            DB::connection()->getPdo();
            $status['checks']['database'] = 'connected';
        } catch (\Exception $e) {
            $status['checks']['database'] = 'disconnected';
            $status['status'] = 'unhealthy';
        }

        // Check articles table and get stats
        try {
            $articleCount = Article::count();
            $status['checks']['articles_count'] = $articleCount;

            $latestArticle = Article::latest('published_at')->first();
            $status['checks']['latest_article'] = $latestArticle
                ? $latestArticle->published_at->toIso8601String()
                : null;
        } catch (\Exception $e) {
            $status['checks']['articles'] = 'error';
        }

        // Determine HTTP status code
        $httpCode = $status['status'] === 'healthy' ? 200 : 503;

        return response()->json($status, $httpCode);
    }
}