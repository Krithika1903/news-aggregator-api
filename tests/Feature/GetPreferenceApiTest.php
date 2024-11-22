<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserAuthorPreferences;
use App\Models\UserCategoryPreferences;
use App\Models\UserSourcePreferences;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Mockery;
use Tests\TestCase;

class GetPreferenceApiTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_get_user_preferences_success() {
        // Arrange: Create a test user and generate a token
        $user = User::factory()->create(); // Create a user using a factory
        $token = $user->createToken('Test App')->plainTextToken; // Create a token for the user

        // Mock the responses for category, source, and author preferences
        $categories = collect([
            ['id' => 1, 'category' => 'Technology'],
            ['id' => 2, 'category' => 'Science'],
        ]);
        $sources = collect([
            ['id' => 1, 'source' => 'The Guardian'],
            ['id' => 2, 'source' => 'BBC'],
        ]);
        $authors = collect([
            ['id' => 1, 'author' => 'John Doe'],
            ['id' => 2, 'author' => 'Jane Smith'],
        ]);

        // Mocking the model methods to return the above collections
        $categoryMock = Mockery::mock('alias:' . UserCategoryPreferences::class);
        $categoryMock->shouldReceive('getData')
            ->once()
            ->with(['user_id' => $user->id])
            ->andReturn($categories);

        $sourceMock = Mockery::mock('alias:' . UserSourcePreferences::class);
        $sourceMock->shouldReceive('getData')
            ->once()
            ->with(['user_id' => $user->id])
            ->andReturn($sources);

        $authorMock = Mockery::mock('alias:' . UserAuthorPreferences::class);
        $authorMock->shouldReceive('getData')
            ->once()
            ->with(['user_id' => $user->id])
            ->andReturn($authors);

        // Act: Call the API endpoint
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token, // Attach the token to the request
        ])->getJson('/api/v1/news/preference'); // Adjust the route based on your API

        // Assert: Check if the response status is 200 (OK)
        $response->assertStatus(200);

        // Assert: Check the response structure
        $response->assertJsonStructure([
            'error',
            'message',
            'data' => [
                'categories',
                'sources',
                'authors',
            ]
        ]);

        // Assert: Check the response content
        $response->assertJson([
            'error' => false,
            'message' => 'User preferences fetched successfully.',
            'data' => [
                'categories' => [
                    ['id' => 1, 'category' => 'Technology'],
                    ['id' => 2, 'category' => 'Science'],
                ],
                'sources' => [
                    ['id' => 1, 'source' => 'The Guardian'],
                    ['id' => 2, 'source' => 'BBC'],
                ],
                'authors' => [
                    ['id' => 1, 'author' => 'John Doe'],
                    ['id' => 2, 'author' => 'Jane Smith'],
                ],
            ],
        ]);
    }
}
