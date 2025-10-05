<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ArticleFilterRequest;
use App\Http\Resources\ArticleResource;
use App\Models\Article;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * Handles article-related API operations
 */
class ArticleController extends Controller
{
    use ApiResponseTrait;

    /**
     * Get paginated list of articles with optional filters
     * Supports: search, date range, category, source, author filters
     */
    public function index(Request $request): AnonymousResourceCollection|JsonResponse
    {
        try {
            // Start query with relationships
            $query = Article::with(['source', 'category'])
                ->orderBy('published_at', 'desc');

            // Apply search filter
            if ($request->has('search')) {
                $query->search($request->search);
            }

            // Apply date range filter
            if ($request->has('from') || $request->has('to')) {
                $query->filterByDate($request->from, $request->to);
            }

            // Apply category filter
            if ($request->has('category')) {
                $query->filterByCategory($request->category);
            }

            // Apply source filter
            if ($request->has('source')) {
                $query->filterBySource($request->source);
            }

            // Apply author filter
            if ($request->has('author')) {
                $query->filterByAuthor($request->author);
            }

            // Paginate results (max 100 per page)
            $perPage = min($request->get('per_page', 15), 100);
            $articles = $query->paginate($perPage);

            return ArticleResource::collection($articles);

        } catch (\Exception $e) {
            Log::error('Error fetching articles: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return $this->errorResponse('Error fetching articles', 500);
        }
    }

    /**
     * Get single article by ID with relationships
     */
    public function show(Article $article): ArticleResource|JsonResponse
    {
        try {
            // Eager load relationships
            $article->load(['source', 'category']);
            return new ArticleResource($article);

        } catch (\Exception $e) {
            Log::error('Error fetching article: ' . $e->getMessage(), [
                'article_id' => $article->id ?? null,
                'trace' => $e->getTraceAsString()
            ]);

            return $this->errorResponse('Error fetching article', 500);
        }
    }

    /**
     * Get list of unique authors
     * Excludes null, empty, and 'Unknown' authors
     */
    public function authors(): JsonResponse
    {
        try {
            $authors = Article::select('author')
                ->whereNotNull('author')
                ->where('author', '!=', '')
                ->where('author', '!=', 'Unknown')
                ->distinct()
                ->orderBy('author')
                ->pluck('author');

            return $this->successResponse($authors, 'Authors retrieved successfully');

        } catch (\Exception $e) {
            Log::error('Error fetching authors: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return $this->errorResponse('Error fetching authors', 500);
        }
    }

    /**
     * Search articles by query string
     * Requires: q (min 2, max 255 chars)
     */
    public function search(Request $request): AnonymousResourceCollection|JsonResponse
    {
        try {
            // Validate search parameters
            $validator = Validator::make($request->all(), [
                'q' => 'required|string|min:2|max:255',
                'per_page' => 'nullable|integer|min:1|max:100',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            // Execute search query
            $query = Article::with(['source', 'category'])
                ->search($request->q)
                ->orderBy('published_at', 'desc');

            // Paginate results
            $perPage = min($request->get('per_page', 15), 100);
            $articles = $query->paginate($perPage);

            return ArticleResource::collection($articles);

        } catch (\Exception $e) {
            Log::error('Error searching articles: ' . $e->getMessage(), [
                'query' => $request->q ?? null,
                'trace' => $e->getTraceAsString()
            ]);

            return $this->errorResponse('Error searching articles', 500);
        }
    }

    /**
     * Get recent articles within specified days
     * Default: 7 days, Max: 30 days
     */
    public function recent(Request $request): AnonymousResourceCollection|JsonResponse
    {
        try {
            // Validate days parameter
            $validator = Validator::make($request->all(), [
                'days' => 'nullable|integer|min:1|max:30',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            // Get recent articles within date range
            $days = min($request->get('days', 7), 30);

            $articles = Article::with(['source', 'category'])
                ->recent($days)
                ->orderBy('published_at', 'desc')
                ->paginate(15);

            return ArticleResource::collection($articles);

        } catch (\Exception $e) {
            Log::error('Error fetching recent articles: ' . $e->getMessage(), [
                'days' => $request->get('days'),
                'trace' => $e->getTraceAsString()
            ]);

            return $this->errorResponse('Error fetching recent articles', 500);
        }
    }

    /**
     * Get articles filtered by source slug
     */
    public function bySource(string $slug, Request $request): AnonymousResourceCollection|JsonResponse
    {
        try {
            // Query articles by source slug
            $query = Article::with(['source', 'category'])
                ->bySourceSlug($slug)
                ->orderBy('published_at', 'desc');

            // Paginate results
            $perPage = min($request->get('per_page', 15), 100);
            $articles = $query->paginate($perPage);

            // Check if any articles found
            if ($articles->isEmpty()) {
                return $this->notFoundResponse('No articles found for this source');
            }

            return ArticleResource::collection($articles);

        } catch (\Exception $e) {
            Log::error('Error fetching articles by source: ' . $e->getMessage(), [
                'source_slug' => $slug,
                'trace' => $e->getTraceAsString()
            ]);

            return $this->errorResponse('Error fetching articles by source', 500);
        }
    }

    /**
     * Get articles filtered by category slug
     */
    public function byCategory(string $slug, Request $request): AnonymousResourceCollection|JsonResponse
    {
        try {
            // Query articles by category slug
            $query = Article::with(['source', 'category'])
                ->byCategorySlug($slug)
                ->orderBy('published_at', 'desc');

            // Paginate results
            $perPage = min($request->get('per_page', 15), 100);
            $articles = $query->paginate($perPage);

            // Check if any articles found
            if ($articles->isEmpty()) {
                return $this->notFoundResponse('No articles found for this category');
            }

            return ArticleResource::collection($articles);

        } catch (\Exception $e) {
            Log::error('Error fetching articles by category: ' . $e->getMessage(), [
                'category_slug' => $slug,
                'trace' => $e->getTraceAsString()
            ]);

            return $this->errorResponse('Error fetching articles by category', 500);
        }
    }
}