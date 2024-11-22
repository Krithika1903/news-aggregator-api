<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use App\Models\NewsArticle;
use App\Models\NewsArticles;
use Mockery;

class ArticleApiTest extends TestCase
{

    /**
     * Test successful article fetch with valid filters.
     *
     * @return void
     */
    public function testGetArticlesSuccess() {
        // Set up the query parameters
        $queryParams = [
            'category' => 'Technology', // optional
        ];

        // Make a GET request with query parameters
        $response = $this->get('/api/v1/articles?' . http_build_query($queryParams));

        // Assert the response status and content
        $response->assertStatus(200)
                 ->assertJson([
                     'error' => false,
                     'message' => 'Articles fetched successfully.',
                 ]);
    }

    /**
     * Test article fetch with an invalid limit.
     *
     * @return void
     */
    public function testGetArticlesInvalidLimit(){
        // Send a GET request with invalid limit parameter
        $response = $this->get('/api/v1/articles?limit=100');

        // Assert status code and error message
        $response->assertStatus(422)
                 ->assertJson([
                     'error' => true,
                     'message' => 'Limit should be between 1 and 50.'
                 ]);
    }

    /**
     * Test article fetch with no articles found.
     *
     * @return void
     */
    public function testGetArticlesNoArticlesFound(){
        // Send a GET request with parameters that do not match any article
        $response = $this->get('/api/v1/articles?keyword=nonexistent');

        // Assert status code and error message
        $response->assertStatus(404)
                 ->assertJson([
                     'error' => true,
                     'message' => 'No articles found.'
                 ]);
    }

    /**
     * Test validation failure when invalid date format is provided.
     *
     * @return void
     */
    public function testGetArticlesInvalidDateFormat() {
        // Send a GET request with an invalid date format
        $response = $this->get('/api/v1/articles?date=invalid-date-format');

        // Assert status code and error message
        $response->assertStatus(422)
                 ->assertJson([
                     'error' => true,
                     'message' => 'The date field must be a valid date.'
                 ]);
    }

    /**
     * Test article fetch with valid cache retrieval.
     *
     * @return void
     */
    public function testGetArticlesWithCache() {
        // Mock the cache to return a preset response
        $cacheData = [
            'data' => [
                ['id' => 1, 'title' => 'Article 1', 'category' => 'Technology'],
                ['id' => 2, 'title' => 'Article 2', 'category' => 'Health']
            ]
        ];

        Cache::shouldReceive('remember')
             ->once()
             ->andReturn($cacheData);

        // Send a GET request to fetch articles
        $response = $this->get('/api/v1/articles?limit=5');

        // Assert that the response returns data from the cache
        $response->assertStatus(200)
                 ->assertJson([
                     'error' => false,
                     'message' => 'Articles fetched successfully.',
                     'articles' => $cacheData
                 ]);
    }

    
}
