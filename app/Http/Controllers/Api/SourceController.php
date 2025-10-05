<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SourceResource;
use App\Models\Source;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SourceController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $sources = Source::withCount('articles')
            ->orderBy('name')
            ->get();

        return SourceResource::collection($sources);
    }

    public function show(Source $source): SourceResource
    {
        $source->loadCount('articles');
        return new SourceResource($source);
    }
}