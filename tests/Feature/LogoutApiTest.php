<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class LogoutApiTest extends TestCase
{
    public function test_user_can_logout_successfully() {
        // Step 1: Create a user and generate an API token
        $user = User::factory()->create(); // Create a user using a factory
        $token = $user->createToken('Test App')->plainTextToken; // Create an API token for the user

        // Step 2: Simulate an authenticated user by passing the token in the Authorization header
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token, // Attach the token to the request
        ])->postJson('/api/v1/logout'); // Perform the logout request

        // Step 3: Assert that the response is successful and contains the expected message
        $response->assertStatus(200)
                ->assertJson([
                    'error' => false,
                    'message' => 'You have been logged out successfully.'
                ]);

        // Step 4: Ensure that the token is deleted (the user is logged out)
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->id, // The user ID
            'name' => 'Test App', // Token name
        ]);
    }

    public function test_user_cannot_logout_with_invalid_token() {
        // Perform the logout request with an invalid token
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . 'invalid_token', // Invalid token
        ])->postJson('/api/v1/logout');

        // Assert: Check for the 401 Unauthorized status
        $response->assertStatus(401)
                ->assertJson([
                    'message' => 'Unauthenticated.',
                ]);
    }

}
