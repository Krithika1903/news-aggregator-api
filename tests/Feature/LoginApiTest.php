<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginApiTest extends TestCase
{

     /** @test */
    public function it_logs_in_successfully() {
        $user = User::factory()->create([
            'email' => 'user1@example.com',
            'password' => Hash::make('password123'),
            'first_name' => 'Berta',
            'last_name' => 'Raynor',
            'phone' => '4745070256',
        ]);
    
        // Act: Make the login request with valid credentials
        $response = $this->postJson('/api/v1/user/login', [
            'login_username' => 'user1@example.com',
            'password' => 'password123'
        ]);
    
        // Assert: Check the response status and structure
        $response->assertStatus(200)
                 ->assertJson([
                     'error' => false,
                     'message' => 'Login successful.',
                     'token' => true,  // Ensure the token is not empty
                     'data' => [
                         'first_name' => 'Berta',
                         'last_name' => 'Raynor',
                         'email' => 'user1@example.com',
                         'phone' => '4745070256',
                     ]
                 ]);
    }

    /** @test */
    public function it_returns_error_for_missing_fields() {
        // Act: Make the login request with missing fields
        $response = $this->postJson('/api/v1/user/login', [
            'login_username' => 'someuser@example.com',
            // Missing 'password' field
        ]);

        // Assert: Check if validation error occurs
        $response->assertStatus(422)
                 ->assertJson([
                     'error' => true,
                     'message' => 'The password field is required.'
                 ]);
    }

    /** @test */
    public function it_returns_error_for_non_existent_user() {
        // Act: Attempt login with a non-existent user
        $response = $this->postJson('/api/v1/user/login', [
            'login_username' => 'nonexistent@example.com',
            'password' => 'password123'
        ]);

        // Assert: Ensure correct error response for non-existent user
        $response->assertStatus(404)
                 ->assertJson([
                     'error' => true,
                     'message' => 'Email or phone number does not exist.'
                 ]);
    }

    /** @test */
    public function it_returns_error_for_incorrect_password() {
        // Arrange: Create a user with a specific password
        $user = User::factory()->create([
            'email' => 'someuser@example.com',
            'password' => Hash::make('password123'),
        ]);

        // Act: Attempt login with the correct username but wrong password
        $response = $this->postJson('/api/v1/user/login', [
            'login_username' => 'someuser@example.com',
            'password' => 'wrongpassword'
        ]);

        // Assert: Ensure error for incorrect password
        $response->assertStatus(422)
                 ->assertJson([
                     'error' => true,
                     'message' => 'Password entered is incorrect.'
                 ]);
    }

}
