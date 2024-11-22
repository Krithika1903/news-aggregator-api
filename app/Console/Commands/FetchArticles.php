<?php

namespace App\Console\Commands;

use App\Models\Categories;
use App\Models\NewsArticles;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class FetchArticles extends Command
{
    protected $signature = 'app:fetch-articles';      // Defines the command signature for running this command.
    protected $description = 'Fetch and store articles from news APIs';

    public function handle() {
        try {
            // Fetch all categories from the database
            $categories = $this->getCategories();

            // Fetch articles from NewsAPI and process them.
            $this->processArticles($this->fetchNewsApi($categories),'NewsAPI');

            // Fetch articles from The Guardian API and process them.
            $this->processArticles($this->fetchGuardian($categories), 'The Guardian');

            // Fetch articles from The New York Times API and process them.
            $this->processArticles($this->fetchNytimes($categories),'New York Times');

            $this->info('Articles fetched and stored successfully.');    // Display a success message
        } catch (\Exception $e) {
            $this->error('Error fetching articles: ' . $e->getMessage());
        }
    }

     // Fetch categories dynamically from the 'categories' table
    private function getCategories(): array {
        return Categories::pluck('category')->toArray(); // Assuming the table has a 'name' column
    }

    // Processes and stores articles.
    private function processArticles(array $articles, string $defaultSource = null) {
        // Iterate through the list of articles provided.
        foreach ($articles['articles'] as $articleData) {
            try {
                // Parse the published date if available;
                $publishedAt = isset($articleData['publishedAt'])
                    ? Carbon::parse($articleData['publishedAt'])->toDateTimeString()
                    : now();

                $source = $defaultSource;  // Use the default source provided

                // Insert or update the article in the database
                NewsArticles::updateOrCreate(  
                    [
                        'title' => $articleData['title'] 
                            ?? $articleData['webTitle'] 
                            ?? $articleData['abstract'] 
                            ?? '',
                        'source' => $source,
                        'url' => $articleData['urlToImage'] ?? $articleData['web_url'] ?? $articleData['webUrl'] ?? '',
                    ],
                    [
                        // Define additional fields for the article.
                        'description' => $articleData['description'] 
                            ?? $articleData['lead_paragraph'] 
                            ?? $articleData['snippet'] 
                            ?? $articleData['webTitle'] 
                            ?? null,
                        'author' => $articleData['author'] ?? null,       // The author of the article, if available.
                        'category' => $articleData['category'] ?? '',     // The category of the article, if provided.
                        'published_at' => $publishedAt,     // The published date of the article.
                    ]
                );
            } catch (\Exception $e) {
                // Log an error message if processing the article fails.
                $this->error("Failed to process article: {$e->getMessage()}");
            }
        }
    }

    // Fetches articles from the News API.
    
    public function fetchNewsApi(array $categories): array {
        $apiKey = config('services.news_apis.news_api');
        return $this->fetchArticlesByCategory(
            "https://newsapi.org/v2/top-headlines?category={category}&apiKey={$apiKey}",
            'articles',
            $categories
        );
    }
    
    // Fetches articles from The Guardian API.
    public function fetchGuardian(array $categories): array {
        $apiKey = config('services.news_apis.guardian');
        return $this->fetchArticlesByCategory(
            "https://content.guardianapis.com/search?q={category}&api-key={$apiKey}",
            'response.results',
            $categories
        );
    }

    // Fetches articles from the New York Times API.
    public function fetchNytimes(array $categories): array {
        $apiKey = config('services.news_apis.nytimes');
        $response = $this->fetchArticlesByCategory(
            "https://api.nytimes.com/svc/search/v2/articlesearch.json?q={category}&api-key={$apiKey}",
            'response.docs',
            $categories
        );

        foreach ($response['articles'] as &$article) {
            $article['source'] = 'New York Times';
        }

        return $response;
    }

    // Fetches articles by category and caches the results.
    private function fetchArticlesByCategory(string $urlTemplate, string $responseKey, array $categories): array {
        // Initialize an array to hold all articles
        $allArticles = [];

        // Loop through each category
        foreach ($categories as $category) {
            // Generate a unique cache key by hashing the URL template and category.
            $cacheKey = md5($urlTemplate . $category);

            // Use caching to avoid redundant API calls.
            $articles = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($urlTemplate, $category, $responseKey) {
                $response = Http::get(str_replace('{category}', $category, $urlTemplate));

                // If the API call fails, return an empty array 
                if ($response->failed()) {
                    return [];
                }
                // Extract the relevant data from the API response
                return data_get($response->json(), $responseKey, []);
            });

            // Assign the category to each article fetched for better identification
            foreach ($articles as &$article) {
                $article['category'] = $category;
            }

            // Merge the current category's articles with the overall articles array.
            $allArticles = array_merge($allArticles, $articles);
        }

        // Return the collected articles
        return ['articles' => $allArticles];
    }
}
