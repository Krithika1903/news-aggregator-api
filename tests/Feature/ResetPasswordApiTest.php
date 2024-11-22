<?php

namespace Tests\Feature;

use App\Models\OtpLogs;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class ResetPasswordApiTest extends TestCase
{

    /** @test */
    // public function it_resets_password_successfully() {
    //     // Prepare data for valid request
    //     $requestData = [
    //         'otp' => '123456', // Example OTP
    //         'otp_token' => 'abcdef123456', // Example OTP token
    //         'new_password' => 'newpassword123',
    //         'new_password_confirmation' => 'newpassword123'
    //     ];
    
    //     // Perform the request
    //     Log::info('Request Data:', $requestData);
    //     $response = $this->postJson('/api/v1/user/reset-password', $requestData);
    //     $response->dump(); 
    
    //     // If the status is not 200, dump the response for debugging
    //     if ($response->status() != 200) {
    //         $response->dump(); // This will dump the full response content (including validation errors)
    //     }
    
    //     // Assert: The response is successful and returns a success message
    //     $response->assertStatus(200)
    //              ->assertJson([
    //                 'error' => false,
    //                 'message' => 'Password updated successfully.'
    //              ]);
    // }
    

    /** @test */
    public function it_returns_error_for_invalid_otp() {
        // Prepare data for invalid OTP
        $requestData = [
            'otp' => '72980',
            'otp_token' => '0O9YfQRn7V2jlxIrdAaM3GevmTNWXbsFP65',
            'new_password' => 'newpassword123',
            'new_password_confirmation' => 'newpassword123'
        ];

        // Mock OTP validation to simulate invalid OTP
        $otpLogs = \Mockery::mock(OtpLogs::class);
        $otpLogs->shouldReceive('validateOtp')
                ->with(['otp' => '72980', 'token' => '0O9YfQRn7V2jlxIrdAaM3GevmTNWXbsFP65'])
                ->andReturn(null); // Simulate invalid OTP

        // Perform the request
        $response = $this->postJson('/api/v1/user/reset-password', $requestData);

        // Assert: The response returns a 422 error with an "Invalid OTP" message
        $response->assertStatus(422)
                ->assertJson([
                    'error' => true,
                    'message' => 'Invalid OTP.'
                ]);
    }

    /** @test */
    // public function it_returns_error_for_expired_otp() {
    //     // Prepare data for expired OTP
    //     $requestData = [
    //         'otp' => '93165',
    //         'otp_token' => 'itkuNGvZbsQx8aU5XFBYwh7T9MfyL4Rdpl3',
    //         'new_password' => 'newpassword123',
    //         'new_password_confirmation' => 'newpassword123'
    //     ];

    //     // Mock OTP validation to simulate expired OTP
    //     $otpLogs = \Mockery::mock(OtpLogs::class);
    //     $otpLogs->shouldReceive('validateOtp')
    //             ->with(['otp' => '93165', 'token' => 'itkuNGvZbsQx8aU5XFBYwh7T9MfyL4Rdpl3'])
    //             ->andReturn((object)[
    //                 'users_id' => 1,
    //                 'expiry_date' => now()->subMinutes(10) // Simulate expired OTP
    //             ]);

    //     // Perform the request
    //     $response = $this->postJson('/api/v1/user/reset-password', $requestData);

    //     // Assert: The response returns a 422 error with an "OTP has been expired" message
    //     $response->assertStatus(422)
    //             ->assertJson([
    //                 'error' => true,
    //                 'message' => 'OTP has been expired.'
    //             ]);
    // }

    /** @test */
    public function it_returns_error_for_failed_validation() {
        // Prepare data with missing new password
        $requestData = [
            'otp' => '123456',
            'otp_token' => 'valid_token',
            'new_password' => '' // Empty password to trigger validation error
        ];

        // Perform the request
        $response = $this->postJson('/api/v1/user/reset-password', $requestData);

        // Assert: The response returns a validation error
        $response->assertStatus(422)
                ->assertJson([
                    'error' => true,
                    'message' => 'The new password field is required.'
                ]);
    }
}
