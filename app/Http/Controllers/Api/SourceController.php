<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SourceResource;
use App\Models\Source;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Handles news source-related API operations
 */
class SourceController extends Controller
{
    /**
     * Get all news sources with article counts
     * Ordered alphabetically by name
     */
    public function index(): AnonymousResourceCollection
    {
        $sources = Source::withCount('articles')
            ->orderBy('name')
            ->get();

        return SourceResource::collection($sources);
    }

    /**
     * Get single source by ID or slug
     * Includes article count
     */
    public function show(Source $source): SourceResource
    {
        $source->loadCount('articles');
        return new SourceResource($source);
    }
}