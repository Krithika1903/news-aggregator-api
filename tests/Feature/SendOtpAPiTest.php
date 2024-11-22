<?php

namespace Tests\Feature;

use App\Models\OtpLogs;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SendOtpAPiTest extends TestCase
{

    /** @test */
    public function it_sends_otp_successfully_for_valid_user() {
        // Create a user for testing
        $user = User::factory()->create([
            'email' => 'testuser@example.com',
            'password' => Hash::make('password123'),
        ]);

        // Prepare the request data
        $requestData = [
            'email' => 'testuser@example.com'
        ];

        // Perform the API request
        $response = $this->postJson('/api/v1/user/send/otp', $requestData);

        // Assert: The response is successful and returns OTP, token, and expiry
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'error',
                    'message',
                    'otp',
                    'otp_token',
                    'otp_expiry'
                ]);

        // Check if OTP logs were created
        $this->assertDatabaseHas('otp_logs', [
            'users_id' => $user->id,
            'otp' => $response->json('otp')
        ]);
    }

    /** @test */
    public function it_returns_error_for_invalid_email() {
        // Prepare the request data with an invalid email
        $requestData = [
            'email' => 'nonexistentuser@example.com'
        ];

        // Perform the API request
        $response = $this->postJson('/api/v1/user/send/otp', $requestData);

        // Assert: The response returns an error indicating email not found
        $response->assertStatus(404)
                 ->assertJson([
                     'error' => true,
                     'message' => 'Email address not found.'
                 ]);
    }

    /** @test */
    public function it_returns_error_for_validation_failure() {
        // Prepare invalid request data (missing email)
        $requestData = [];

        // Perform the API request
        $response = $this->postJson('/api/v1/user/send/otp', $requestData);

        // Assert: The response returns validation errors
        $response->assertStatus(422)
                 ->assertJson([
                     'error' => true,
                     'message' => 'The email field is required.'
                 ]);
    }

}
