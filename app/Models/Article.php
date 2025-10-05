<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class Article extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    protected $with = ['source', 'category'];

    public function source(): BelongsTo
    {
        return $this->belongsTo(Source::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        if (!$search) {
            return $query;
        }

        return $query->where(function ($q) use ($search) {
            $q->whereFullText(['title', 'description', 'content'], $search)
              ->orWhere('title', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%")
              ->orWhere('content', 'like', "%{$search}%");
        });
    }

    public function scopeFilterByDate(Builder $query, ?string $from, ?string $to): Builder
    {
        if ($from) {
            $query->where('published_at', '>=', $from);
        }

        if ($to) {
            $query->where('published_at', '<=', $to);
        }

        return $query;
    }

    public function scopeFilterByCategory(Builder $query, $categoryIds): Builder
    {
        if (!$categoryIds) {
            return $query;
        }

        $ids = is_array($categoryIds) ? $categoryIds : explode(',', $categoryIds);
        return $query->whereIn('category_id', $ids);
    }

    public function scopeFilterBySource(Builder $query, $sourceIds): Builder
    {
        if (!$sourceIds) {
            return $query;
        }

        $ids = is_array($sourceIds) ? $sourceIds : explode(',', $sourceIds);
        return $query->whereIn('source_id', $ids);
    }

    public function scopeFilterByAuthor(Builder $query, $authors): Builder
    {
        if (!$authors) {
            return $query;
        }

        $authorList = is_array($authors) ? $authors : explode(',', $authors);
        return $query->whereIn('author', $authorList);
    }

    public function scopeRecent(Builder $query, int $days = 7): Builder
    {
        return $query->where('published_at', '>=', now()->subDays($days));
    }

    public function scopeBySourceSlug(Builder $query, string $slug): Builder
    {
        return $query->whereHas('source', function ($q) use ($slug) {
            $q->where('slug', $slug);
        });
    }

    public function scopeByCategorySlug(Builder $query, string $slug): Builder
    {
        return $query->whereHas('category', function ($q) use ($slug) {
            $q->where('slug', $slug);
        });
    }
}