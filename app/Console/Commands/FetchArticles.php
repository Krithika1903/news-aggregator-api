<?php

namespace App\Console\Commands;

use App\Models\NewsArticles;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class FetchArticles extends Command
{
    protected $signature = 'app:fetch-articles';
    protected $description = 'Fetch and store articles from news APIs';

    public function handle() {
        try {
            $this->processArticles($this->fetchNewsApi());
            $this->processArticles($this->fetchGuardian(), 'The Guardian');
            $this->processArticles($this->fetchNytimes());
            $this->info('Articles fetched and stored successfully.');
        } catch (\Exception $e) {
            $this->error('Error fetching articles: ' . $e->getMessage());
        }
    }

    // Processes and stores articles.
    private function processArticles(array $articles, string $defaultSource = null) {
        foreach ($articles['articles'] as $articleData) {
            try {
                $publishedAt = isset($articleData['publishedAt'])
                    ? Carbon::parse($articleData['publishedAt'])->toDateTimeString()
                    : now();

                $source = $articleData['source']['name'] 
                    ?? $articleData['source'] 
                    ?? $defaultSource;

                NewsArticles::updateOrCreate(
                    [
                        'title' => $articleData['title'] 
                            ?? $articleData['webTitle'] 
                            ?? $articleData['abstract'] 
                            ?? '',
                        'source' => $source,
                        'url' => $articleData['url'] ?? $articleData['webUrl'] ?? '',
                    ],
                    [
                        'description' => $articleData['description'] 
                            ?? $articleData['lead_paragraph'] 
                            ?? $articleData['snippet'] 
                            ?? $articleData['webTitle'] 
                            ?? null,
                        'author' => $articleData['author'] ?? null,
                        'category' => $articleData['category'] ?? '',
                        'published_at' => $publishedAt,
                    ]
                );
            } catch (\Exception $e) {
                $this->error("Failed to process article: {$e->getMessage()}");
            }
        }
    }

    // Fetches articles from the News API.
    
    public function fetchNewsApi(): array {
        $apiKey = config('services.news_apis.news_api');
        return $this->fetchArticlesByCategory(
            "https://newsapi.org/v2/top-headlines?category={category}&apiKey={$apiKey}",
            'articles'
        );
    }
    
    // Fetches articles from The Guardian API.
    public function fetchGuardian(): array {
        $apiKey = config('services.news_apis.guardian');
        return $this->fetchArticlesByCategory(
            "https://content.guardianapis.com/search?q={category}&api-key={$apiKey}&show-fields=headline,body",
            'response.results'
        );
    }

    // Fetches articles from the New York Times API.
    public function fetchNytimes(): array {
        $apiKey = config('services.news_apis.nytimes');
        $response = $this->fetchArticlesByCategory(
            "https://api.nytimes.com/svc/search/v2/articlesearch.json?q={category}&api-key={$apiKey}",
            'response.docs'
        );

        // Add default source name for NYTimes
        foreach ($response['articles'] as &$article) {
            $article['source'] = 'The New York Times';
        }

        return $response;
    }

    // Fetches articles by category and caches the results.
    private function fetchArticlesByCategory(string $urlTemplate, string $responseKey): array {
        $categories = config('categories.categories');
        $allArticles = [];

        foreach ($categories as $category) {
            $cacheKey = md5($urlTemplate . $category);

            $articles = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($urlTemplate, $category, $responseKey) {
                $response = Http::get(str_replace('{category}', $category, $urlTemplate));

                if ($response->failed()) {
                    return [];
                }

                return data_get($response->json(), $responseKey, []);
            });

            foreach ($articles as &$article) {
                $article['category'] = $category;
            }

            $allArticles = array_merge($allArticles, $articles);
        }

        return ['articles' => $allArticles];
    }
}
