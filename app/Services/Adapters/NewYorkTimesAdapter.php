<?php

namespace App\Services\Adapters;

use App\Contracts\NewsSourceAdapter;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class NewYorkTimesAdapter implements NewsSourceAdapter
{
    private Client $client;
    private string $apiKey;
    private string $baseUrl;

    public function __construct()
    {
        $this->client = new Client(['timeout' => 30]);
        $this->apiKey = config('services.nyt.key');
        $this->baseUrl = config('services.nyt.url');
    }

    public function fetchArticles(int $limit = 100): array
    {
        try {
            //Fetch articles 
            $response = $this->client->get("{$this->baseUrl}/search/v2/articlesearch.json", [
                'query' => [
                    'api-key' => $this->apiKey,
                    'sort' => 'newest',
                    'page' => 0,
                ]
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            if ($data['status'] !== 'OK') {
                Log::warning('NYT API returned non-OK status', ['response' => $data]);
                return [];
            }

            //Limit and transform articles
            $articles = array_slice($data['response']['docs'] ?? [], 0, $limit);


            return array_map(function ($article) {
                return $this->transformArticle($article);
            }, $articles);

        } catch (GuzzleException $e) {
            // Log Guzzle-specific errors
            Log::error('NYT API fetch error: ' . $e->getMessage(), [
                'code' => $e->getCode(),
                'source' => $this->getSourceName()
            ]);
            return [];
        } catch (\Exception $e) {
            Log::error('NYT API unexpected error: ' . $e->getMessage());
            return [];
        }
    }

    public function getSourceName(): string
    {
        return 'New York Times';
    }

    //Transforms raw NYT article data into standardized format.
    private function transformArticle(array $article): array
    {

        //Extract first valid image URL
        $imageUrl = null;
        if (!empty($article['multimedia'])) {
            foreach ($article['multimedia'] as $media) {
                if (isset($media['url']) && $media['url']) {
                    $imageUrl = 'https://www.nytimes.com/' . $media['url'];
                    break;
                }
            }
        }

        // Extract author
        $author = 'New York Times';
        if (!empty($article['byline']['original'])) {
            $author = str_replace('By ', '', $article['byline']['original']);
        } elseif (!empty($article['byline']['person'])) {
            $names = array_map(fn($p) => $p['firstname'] . ' ' . $p['lastname'], $article['byline']['person']);
            $author = implode(', ', $names);
        }

        return [
            'title' => $article['headline']['main'] ?? 'Untitled',
            'description' => $article['abstract'] ?? null,
            'content' => $article['lead_paragraph'] ?? $article['snippet'] ?? $article['abstract'] ?? null,
            'author' => $author,
            'url' => $article['web_url'],
            'image_url' => $imageUrl,
            'published_at' => $article['pub_date'] ?? now(),
            'external_id' => $article['_id'],
            'source_name' => $this->getSourceName(),
            'category' => $article['section_name'] ?? $article['news_desk'] ?? 'general',
        ];
    }
}