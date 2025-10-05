<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Source extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'api_identifier'];

    protected $hidden = ['created_at', 'updated_at'];

    public function articles(): HasMany
    {
        return $this->hasMany(Article::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function activeArticles(): HasMany
    {
        return $this->articles()->where('published_at', '>=', now()->subDays(30));
    }

    public function latestArticles(int $limit = 10): HasMany
    {
        return $this->articles()->latest('published_at')->limit($limit);
    }
}