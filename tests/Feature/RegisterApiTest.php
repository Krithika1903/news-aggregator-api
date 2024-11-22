<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

Class RegisterApiTest extends TestCase
{

    /** @test */
    public function it_returns_error_for_missing_fields() {
        // Arrange: Prepare incomplete data (missing phone_no)
        $data = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
        ];

        // Act: Make the API request
        $response = $this->postJson('/api/v1/user/register', $data);

        // Assert: Check if the response has validation error
        $response->assertStatus(422)
                ->assertJson([
                    'error' => true,
                    'message' => 'The phone no field is required.',
                ]);
    }

    /** @test */
    public function it_returns_error_for_invalid_email() {
        // Arrange: Prepare invalid email
        $data = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'phone_no' => '1234567890',
            'email' => 'invalid-email',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        // Act: Make the API request
        $response = $this->postJson('/api/v1/user/register', $data);

        // Assert: Check if the response has validation error
        $response->assertStatus(422)
                ->assertJson([
                    'error' => true,
                    'message' => 'The email field must be a valid email address.',
                ]);
    }

    /** @test */
    public function it_returns_error_for_existing_phone() {
        // An existing user with the same phone number
        $existingUser = User::create([
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'phone' => '1234567890',  // Same phone number as the new user
            'email' => 'jane@example.com',
            'password' => Hash::make('password123'),
            'user_role' => 'USER',
            'is_active' => 1,
        ]);

        $data = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'phone_no' => '1234567890', // Same phone number as the existing user
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',  // Add the confirmation field
        ];

        // Act: Make the API request
        $response = $this->postJson('/api/v1/user/register', $data);

        // Assert: Check if the response returns the correct error message for phone number conflict
        $response->assertStatus(422)
                ->assertJson([
                    'error' => true,
                    'message' => 'This phone number already exists, use different phone number to register.',
                ]);
    }

    /** @test */
    public function it_returns_error_for_existing_email() {
        // Anexisting user
        $existingUser = User::create([
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'phone' => '0987654321',
            'email' => 'jane@example.com', // Same email as the new user
            'password' => Hash::make('password123'),
            'user_role' => 'USER',
            'is_active' => 1,
        ]);

        $data = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'phone_no' => '3453445454',
            'email' => 'jane@example.com', // Same email as the existing user
            'password' => 'password123',
            'password_confirmation' => 'password123',  // Add the confirmation field
        ];

        // Act: Make the API request
        $response = $this->postJson('/api/v1/user/register', $data);

        // Assert: Check if the response returns the correct error message
        $response->assertStatus(422)
                ->assertJson([
                    'error' => true,
                    'message' => 'This email address already exists, use different email to register.',
                ]);
    }
}
