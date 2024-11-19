<?php

use App\Http\Controllers\Auth\UserAuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
|
|Here you can register authentication routes for users.
|
*/

Route::prefix('v1')->group(function () {
    Route::prefix('user')->group(function () {
        Route::post('register',[UserAuthController::class,'register']);   // Route to handle user registration
        Route::post('login',[UserAuthController::class,'login']);   // Route to handle user login
        Route::post('send/otp', [UserAuthController::class, 'sendOtp']);  // Route to send OTP for email
        Route::post('reset-password',[UserAuthController::class, 'resetPassword']);     // Route to reset the user's password using OTP verification     

    });

    Route::group(['middleware'=>['auth:sanctum']],function(){
        Route::middleware(['abilities:USER'])->group(function () {
            Route::post('logout',[UserAuthController::class, 'logout']);     // Route to logout     
        });
    });

});


    