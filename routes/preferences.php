<?php

use App\Http\Controllers\UserPreferenceController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| User Preferences Routes
|--------------------------------------------------------------------------
|
|Here you can register routes for user preferences.
|
*/

Route::prefix('v1')->group(function () {
    Route::group(['middleware'=>['auth:sanctum']],function(){
        Route::middleware(['abilities:USER'])->group(function () {
            Route::prefix('news')->group(function () {
                Route::prefix('preference')->group(function () {
                    Route::post('/set',[UserPreferenceController::class, 'setUserPreferences']);     // Route to set preference     
                    Route::get('/',[UserPreferenceController::class, 'getUserPreferences']);     // Route to get preferences     
                });
            });
            Route::get('user/preferred-news',[UserPreferenceController::class, 'getUserPreferredNews']);     // Route to get preferred news     
            Route::get('/authors',[UserPreferenceController::class, 'getAuthorList']);     // Route to get authors    
        });
    });
});


    