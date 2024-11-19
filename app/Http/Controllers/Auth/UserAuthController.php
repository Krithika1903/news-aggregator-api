<?php

namespace App\Http\Controllers\Auth;

use App\Helper\Validation\AuthValidation;
use App\Http\Controllers\Controller;
use App\Models\OtpLogs;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserAuthController extends Controller
{
    // User registration function
    public function register(Request $request) {
        $validator = AuthValidation::register($request);    // Validate user input based on registration rules
        
        if ($validator->fails()) {  //display error message if validator fails
            return response([
                'error' => true,
                'message' => $validator->errors()->first()
            ], 422);
        } 
        try {
            $isPhoneExists = User::checkExists(['phone' => $request['phone_no'], 'user_role' => 'USER']);       //check phone number exists
            if ($isPhoneExists) {    //display error message if phone number exists 
                return response([
                    'error' => true,
                    'message' => "This phone number already exists, use different phone number to register."
                ], 422);
            }

            $isEmailExists = User::checkExists(['email' => $request['email'], 'user_role' => 'USER']);       //check email exists
            if ($isEmailExists) {    //display error message if phone number exists 
                return response([
                    'error' => true,
                    'message' => "This email address already exists, use different email to register."
                ], 422);
            }

            // Add new user to the database with hashed password
            User::addUser([
                'first_name' => $request['first_name'],
                'last_name' => $request['last_name'],
                'phone' => $request['phone_no'],
                'email' => $request['email'],
                'user_role' => 'USER',
                'password' => Hash::make($request['password']),
                'is_active' => 1
            ]);

            // Return success response after registration
            return response([
                'error' => false,
                'message' => 'User registered successfully.',
            ], 201); 
        } catch(\Exception $e) {
            return response([
                'error'=>true,
                'message'=> $e->getMessage()                 
            ], 500);            
        }     
    }   

    // User login function
    public function login(Request $request) {
        // Validate the login credentials
        $validator = AuthValidation::login($request);

        if($validator->fails()){  // If validation fails, return the first error message
            return response([
                'error' => true,
                'message' => $validator->errors()->first()
            ], 422);
        }
        
        try {
            // Retrieve the user data based on the login username 
            $user = User::getLoggedUserData($request->login_username, ['is_active' => 1]);
            
            // If user does not exist, return an error response
            if (!$user) { 
                return response([
                    'error' => true,
                    'message' => 'Email or phone number does not exist.'
                ], 404);
            } else if (!Hash::check($request->password, $user->password)) {   // If the password is incorrect, return an error response
                return response([
                    'error' => true,
                    'message' => 'Password entered is incorrect.'
                ], 422);
            } else {   
                // Generate a new token for the logged-in user with their role permissions
                $token = $user->createToken('user-login-token', [$user->user_role])->plainTextToken;
                
                // Return success response with the user data and generated token
                return response([
                    'error' => false,
                    'message'=>'Login successful.',
                    'token'=> $token,
                    'data'=> [
                        'first_name' => $user->first_name,
                        'last_name' => $user->last_name,
                        'email' => $user->email,
                        'phone' => $user->phone
                    ]
                ], 200);
            }
        } catch(\Exception $e) {
            return response([
                'error'=>true,
                'message'=> $e->getMessage()                 
            ], 500);
        }     
    }

    // User logout function
    public function logout(Request $request){
        try {
            // Delete the current access token for the user to log them out
            $request->user()->currentAccessToken()->delete();

            // Return a success response indicating that the user has been logged out
            return response([
                'error' => false,
                'message' => 'You have been logged out successfully.',
            ], 200);
        } catch(\Exception $e) {
            return response([
                'error'=>true,
                'message'=> $e->getMessage()                 
            ], 500);
        } 
    }

    // Send OTP function
    public function sendOtp(Request $request) {
        $validator = AuthValidation::sendOtp($request);
        if($validator->fails()) {       //display error message if validator fails   
            return response([
                'error' =>  true,
                'message' => $validator->errors()->first()                
            ], 422);
        }

        try {
            $userData = User::getFirstUser(['email' => $request['email'], 'user_role' => 'USER']);    //check if email exists in users table            

            if (!$userData) {   //display error message if email does not exists in users table
                return response([
                    'error' => true,
                    'message' => 'Email address not found.'              
                ], 404);  
            } else {
                // Generate a random OTP 
                $random = str_shuffle('1234567890');
                $otp = substr($random, 1, 5); //generate OTP

                // Generate a random token to associate with the OTP for identification
                $randomStrToken = str_shuffle('1234567890abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ');
                $token = substr($randomStrToken, 1, 35); 
                 
                // Get the current date and time             
                $today = date("Y-m-d H:i:s");

                // Set the OTP expiry time to 5 minutes from the current time
                $otpExpiry = date("Y-m-d H:i:s", strtotime("{$today} +5 minute")); 
 
                OtpLogs::addOtpLogs([       //save otp details to otp_logs table 
                    'users_id' => $userData->id,
                    'otp' => $otp,
                    'token' =>$token,
                    'expiry_date' => $otpExpiry,
                    'created_at' => $today,
                    'updated_at' => $today
                ]);

                // Return a successful response with the OTP, token, and expiry details
                return response([
                    'error' => false,
                    'message' => 'OTP sent successfully.',
                    'otp' => $otp,
                    'otp_token' => $token,
                    'otp_expiry' => $otpExpiry
                ], 200);
            }   
        } catch (\Exception $e) {            
            return response([
                'error' => true,
                'message' => $e->getMessage()                 
            ], 500);            
        }
    }

    // Rest password function
    public function resetPassword(Request $request){
        $validator = AuthValidation::setPassword($request);

        if($validator->fails()){ // If validation fails, return an error response 
            return response([
                'error' => true,
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {            
            $otpLogs = OtpLogs::validateOtp(['otp' => $request->otp,'token' => $request->otp_token]);            
            
            // If the OTP does not exist or is invalid, return an error response
            if (!$otpLogs) {    
                return response([
                    'error' => true,
                    'message' => 'Invalid OTP.'              
                ], 422);  
            } else if ($otpLogs['expiry_date'] < date('Y-m-d H:i:s')) {
                // If the OTP has expired, return an error response
                return response([
                    'error' => true,
                    'message' => 'OTP has been expired.'              
                ], 422);  
            } else {
                // If OTP is valid and not expired, update the user's password
                User::updateUser($otpLogs->users_id, [
                    'password' => Hash::make($request->new_password)
                ]);

                // Return a success response
                return response([
                    'error' => false,
                    'message' => 'Password updated successfully.',
                ], 200);
            }
        } catch(\Exception $e) {
            return response([
                'error' => true,
                'message' => $e->getMessage()                 
            ], 500);            
        }   
    }
}
