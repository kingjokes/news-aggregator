<?php

namespace App\Contracts;

interface NewsSourceAdapter
{
    public function fetchArticles(int $limit = 100): array;
    
    public function getSourceName(): string;
}