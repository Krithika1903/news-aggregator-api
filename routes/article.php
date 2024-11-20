<?php

use App\Http\Controllers\Article\ArticleController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Article Management Routes
|--------------------------------------------------------------------------
|
|Here you can register routes for article management.
|
*/

Route::prefix('v1')->group(function () {
    Route::get('/articles', [ArticleController::class, 'getArticles']);  // Route to get articles
    Route::get('/article/{id}', [ArticleController::class, 'getSingleArticleDet']);  // Route to get individual article details
});


    