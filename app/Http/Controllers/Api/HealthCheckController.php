<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class HealthCheckController extends Controller
{
    public function index(): JsonResponse
    {
        $status = [
            'status' => 'healthy',
            'timestamp' => now()->toIso8601String(),
            'checks' => []
        ];

        try {
            DB::connection()->getPdo();
            $status['checks']['database'] = 'connected';
        } catch (\Exception $e) {
            $status['checks']['database'] = 'disconnected';
            $status['status'] = 'unhealthy';
        }

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

        $httpCode = $status['status'] === 'healthy' ? 200 : 503;

        return response()->json($status, $httpCode);
    }
}