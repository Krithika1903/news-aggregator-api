<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class SetPreferenceApiTest extends TestCase
{


    /**
     * Test invalid category preference.
     *
     * @return void
     */
    public function test_set_user_preferences_invalid_category() {
        // Arrange
        $user = User::factory()->create(); // Create a user
        $token = $user->createToken('Test App')->plainTextToken; // Create the token

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token, // Attach the token
        ])->postJson('/api/v1/news/preference/set', [
            'preferred_category' => ['Technologys'],
            'preferred_source' => ['The Guardians'],
        ]);

        $response->assertStatus(422);

        // Assert that the response message is as expected
        $response->assertJson([
            'error' => true,
            'message' => 'The selected preferred category is invalid.',
        ]);
    }

    /**
     * Test invalid source preference.
     *
     * @return void
     */
    public function test_set_user_preferences_invalid_source() {
        $user = User::factory()->create(); // Create a user
        $token = $user->createToken('Test App')->plainTextToken; // Create the token

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token, // Attach the token
        ])->postJson('/api/v1/news/preference/set', [
            'preferred_category' => ['Technology'],
            'preferred_source' => ['The Guardians'],
        ]);

        $response->assertStatus(422);

        // Assert that the response message is as expected
        $response->assertJson([
            'error' => true,
            'message' => 'The selected preferred source is invalid.',
        ]);
    }

    /**
     * Test invalid author preference.
     *
     * @return void
     */
    public function test_set_user_preferences_invalid_author() {
        // Arrange
        $user = User::factory()->create(); // Create a user
        $token = $user->createToken('Test App')->plainTextToken; // Create the token

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token, // Attach the token
        ])->postJson('/api/v1/news/preference/set', [
            'preferred_author' => ['The_Guardians'],
        ]);

        $response->assertStatus(422);

        // Assert that the response message is as expected
        $response->assertJson([
            'error' => true,
            'message' => 'The selected preferred author is invalid.',
        ]);
    }

    /**
     * Test invalid fields.
     *
     * @return void
     */
    public function test_set_user_preferences_invalid_data() {
        // Arrange
        $user = User::factory()->create(); // Create a user
        $token = $user->createToken('Test App')->plainTextToken; // Create the token

        // Act
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token, // Attach the token
        ])->postJson('/api/v1/news/preference/set', [
            'preferred_author' => '', // Simulating invalid input for author
        ]);

        // Assert
        $response->assertStatus(422); // Assert 422 Unprocessable Entity

        // Assert the structure of the response
        $response->assertJsonStructure([
            'error',  // Assert presence of 'error' field
            'message' // Assert presence of 'message' field
        ]);

        // Optionally, you can also assert the exact error message
        $response->assertJson([
            'error' => true,
            'message' => 'The preferred author field must be an array.' // This should match your validation error message
        ]);
    }
    
}
