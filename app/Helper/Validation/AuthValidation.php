<?php
namespace App\Helper\Validation;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules;

class AuthValidation
{
    // User registration validation rules
    public static function register(Request $request) {
        $rules = [
            'first_name' => ['required','max:50'],
            'last_name' => ['required','max:50'],
            'email' => ['required', 'email'],
            'phone_no' => ['required', 'digits:10'],
            'password' => ['required','confirmed', Rules\Password::defaults()]          
        ];
        
        return Validator::make($request->all(), $rules);
    }

    // User login validation rules
    public static function login(Request $request) {
        $rules = [            
            'login_username' => ['required'],            
            'password' => ['required']
        ];

        return Validator::make($request->all(), $rules);
    }


    // User reset password validation rules
    public static function resetPassword(Request $request) {
        $rules = [
            'current_password' => ['required'],
            'new_password' => ['required', 'confirmed', Rules\Password::defaults()],
        ];

        return Validator::make($request->all(), $rules);
    }

    // Send OTP validation rules
    public static function sendOtp(Request $request) {
        $rules = [            
           'email' => ['required','email']
        ];
        
        return Validator::make($request->all(), $rules);
    }

    // Set password validation rule
    public static function setPassword(Request $request) {
        $rules = [            
           'otp' => ['required'],
           'otp_token' => ['required'],
           'new_password' => ['required', 'confirmed', Rules\Password::defaults()]
        ];
        
        return Validator::make($request->all(), $rules);
    }
}