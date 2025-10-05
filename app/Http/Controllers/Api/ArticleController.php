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

class ArticleController extends Controller
{
    use ApiResponseTrait;

    public function index(Request $request): AnonymousResourceCollection|JsonResponse
    {
        try {
            $query = Article::with(['source', 'category'])
                ->orderBy('published_at', 'desc');

            if ($request->has('search')) {
                $query->search($request->search);
            }

            if ($request->has('from') || $request->has('to')) {
                $query->filterByDate($request->from, $request->to);
            }

            if ($request->has('category')) {
                $query->filterByCategory($request->category);
            }

            if ($request->has('source')) {
                $query->filterBySource($request->source);
            }

            if ($request->has('author')) {
                $query->filterByAuthor($request->author);
            }

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

    public function show(Article $article): ArticleResource|JsonResponse
    {
        try {
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

    public function authors(): JsonResponse
    {
        try {
            $authors = Article::select('author')
                ->whereNotNull('author')
                ->whereNotEmpty()
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

    public function search(Request $request): AnonymousResourceCollection|JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'q' => 'required|string|min:2|max:255',
                'per_page' => 'nullable|integer|min:1|max:100',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $query = Article::with(['source', 'category'])
                ->search($request->q)
                ->orderBy('published_at', 'desc');

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

    public function recent(Request $request): AnonymousResourceCollection|JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'days' => 'nullable|integer|min:1|max:30',
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

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

    public function bySource(string $slug, Request $request): AnonymousResourceCollection|JsonResponse
    {
        try {
            $query = Article::with(['source', 'category'])
                ->bySourceSlug($slug)
                ->orderBy('published_at', 'desc');

            $perPage = min($request->get('per_page', 15), 100);
            $articles = $query->paginate($perPage);

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

    public function byCategory(string $slug, Request $request): AnonymousResourceCollection|JsonResponse
    {
        try {
            $query = Article::with(['source', 'category'])
                ->byCategorySlug($slug)
                ->orderBy('published_at', 'desc');

            $perPage = min($request->get('per_page', 15), 100);
            $articles = $query->paginate($perPage);

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