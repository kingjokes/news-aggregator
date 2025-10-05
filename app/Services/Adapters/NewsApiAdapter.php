<?php

namespace App\Services\Adapters;

use App\Contracts\NewsSourceAdapter;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class NewsApiAdapter implements NewsSourceAdapter
{
    private Client $client;
    private string $apiKey;
    private string $baseUrl;

    public function __construct()
    {
        $this->client = new Client(['timeout' => 30]);
        $this->apiKey = config('services.newsapi.key');
        $this->baseUrl = config('services.newsapi.url');
    }

    public function fetchArticles(int $limit = 100): array
    {
        try {
            $response = $this->client->get("{$this->baseUrl}/top-headlines", [
                'query' => [
                    'apiKey' => $this->apiKey,
                    'language' => 'en',
                    'pageSize' => min($limit, 100),
                ]
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            if ($data['status'] !== 'ok') {
                Log::warning('NewsAPI returned non-ok status', ['response' => $data]);
                return [];
            }

            return array_map(function ($article) {
                return $this->transformArticle($article);
            }, $data['articles'] ?? []);

        } catch (GuzzleException $e) {
            Log::error('NewsAPI fetch error: ' . $e->getMessage(), [
                'code' => $e->getCode(),
                'source' => $this->getSourceName()
            ]);
            return [];
        } catch (\Exception $e) {
            Log::error('NewsAPI unexpected error: ' . $e->getMessage());
            return [];
        }
    }

    public function getSourceName(): string
    {
        return 'NewsAPI';
    }

    private function transformArticle(array $article): array
    {
        return [
            'title' => $article['title'] ?? 'Untitled',
            'description' => $article['description'] ?? null,
            'content' => $article['content'] ?? $article['description'] ?? null,
            'author' => $article['author'] ?? 'Unknown',
            'url' => $article['url'],
            'image_url' => $article['urlToImage'] ?? null,
            'published_at' => $article['publishedAt'] ?? now(),
            'external_id' => md5($article['url']),
            'source_name' => $article['source']['name'] ?? $this->getSourceName(),
            'category' => $this->mapCategory($article),
        ];
    }

    private function mapCategory(array $article): string
    {
        return 'general';
    }
}