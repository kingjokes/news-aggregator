<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Handles category-related API operations
 */
class CategoryController extends Controller
{
    /**
     * Get all categories with article counts
     * Ordered alphabetically by name
     */
    public function index(): AnonymousResourceCollection
    {
        $categories = Category::withCount('articles')
            ->orderBy('name')
            ->get();

        return CategoryResource::collection($categories);
    }

    /**
     * Get single category by ID or slug
     * Includes article count
     */
    public function show(Category $category): CategoryResource
    {
        $category->loadCount('articles');
        return new CategoryResource($category);
    }
}