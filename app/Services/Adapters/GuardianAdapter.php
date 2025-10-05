<?php

namespace App\Services\Adapters;

use App\Contracts\NewsSourceAdapter;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class GuardianAdapter implements NewsSourceAdapter
{
    private Client $client;
    private string $apiKey;
    private string $baseUrl;

    public function __construct()
    {
        $this->client = new Client(['timeout' => 30]);
        $this->apiKey = config('services.guardian.key');
        $this->baseUrl = config('services.guardian.url');
    }

    public function fetchArticles(int $limit = 100): array
    {
        try {

            //fetch news
            $response = $this->client->get("{$this->baseUrl}/search", [
                'query' => [
                    'api-key' => $this->apiKey,
                    'page-size' => min($limit, 50),
                    'show-fields' => 'thumbnail,trailText,bodyText',
                    'show-tags' => 'contributor',
                    'order-by' => 'newest',
                ]
            ]);

            //decode data
            $data = json_decode($response->getBody()->getContents(), true);

            //if error log data
            if ($data['response']['status'] !== 'ok') {
                Log::warning('Guardian API returned non-ok status', ['response' => $data]);
                return [];
            }

               //Transform and return articles
            return array_map(function ($article) {
                return $this->transformArticle($article);
            }, $data['response']['results'] ?? []);

        } catch (GuzzleException $e) {
            Log::error('Guardian API fetch error: ' . $e->getMessage(), [
                'code' => $e->getCode(),
                'source' => $this->getSourceName()
            ]);
            return [];
        } catch (\Exception $e) {
            Log::error('Guardian API unexpected error: ' . $e->getMessage());
            return [];
        }
    }

    //default source name
    public function getSourceName(): string
    {
        return 'The Guardian';
    }


    //Transforms raw Guardian article data into standardized format.
    private function transformArticle(array $article): array
    {
        $author = 'The Guardian';
        if (!empty($article['tags'])) {
            //Extract author from contributor tags
            $contributors = array_filter($article['tags'], fn($tag) => $tag['type'] === 'contributor');
            if (!empty($contributors)) {
                $author = reset($contributors)['webTitle'];
            }
        }

        return [
            'title' => $article['webTitle'],
            'description' => $article['fields']['trailText'] ?? null,
            'content' => $article['fields']['bodyText'] ?? $article['fields']['trailText'] ?? null,
            'author' => $author,
            'url' => $article['webUrl'],
            'image_url' => $article['fields']['thumbnail'] ?? null,
            'published_at' => $article['webPublicationDate'] ?? now(),
            'external_id' => $article['id'],
            'source_name' => $this->getSourceName(),
            'category' => $article['sectionName'] ?? 'general',
        ];
    }
}