<?php

namespace App\Http\Controllers\Article;

use App\Helper\Validation\ArticleValidation;
use App\Helper\Validation\AuthValidation;
use App\Http\Controllers\Controller;
use App\Models\NewsArticles;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ArticleController extends Controller
{
    // Function to view all article details with filter options
    public function getArticles(Request $request) {
        // Validate the incoming request parameters based on predefined validation rule
        $validator = ArticleValidation::articleValidation($request);

        if($validator->fails()){  // If validation fails, return the first error message
            return response([
                'error' => true,
                'message' => $validator->errors()->first()
            ], 422);
        }
        try {
            // Retrieve all query parameters from the request
            $queryParam = $request->query(); 

            // Check if the 'limit' parameter is provided and validate its value
            if (isset($queryParam['limit'])) {
                // Ensure 'limit' is between 1 and 50
                if ($queryParam['limit'] < 1 || $queryParam['limit'] > 50) {
                    return response([
                        'error' => true,
                        'message' => 'Limit should be between 1 and 50.'
                    ],422);
                }
                $limit = $queryParam['limit'];
            } else {
                // Default limit is 10 if no 'limit' is provided
                $limit = 10;
            }

            // Prepare the filters for the articles query based on the request parameters
            $filters = [
                'keyword' => $queryParam['keyword'] ?? null,
                'date' => $queryParam['date'] ?? null,
                'category' => $queryParam['category'] ?? null,
                'source' => $queryParam['source'] ?? null,
            ];

            // Define a unique cache key based on the filters and limit to cache the results
            $cacheKey = 'articles_' . md5(json_encode($filters)) . '_limit_' . $limit;

            // Attempt to retrieve cached articles from the cache store
            $getArticles = Cache::remember($cacheKey, 60, function () use ($filters, $limit) {
                return NewsArticles::getArticles($filters, $limit);  // Fetch from the database if not cached
            });
            
            // If no articles are found, return a 404 response
            if(!count($getArticles['data'])) {
                return response([
                    'error' => true,
                    'message' => 'No articles found.'
                ], 404);
            }

            // If articles are found, return them in the response with a success message
            return response([
                'error' => false,
                'message' => 'Articles fetched successfully.',
                'articles' => $getArticles
            ],200);

        } catch(\Exception $e) {
            return response([
                'error'=>true,
                'message'=> $e->getMessage()                 
            ], 500);            
        }
    }

    // Function to view single article details
    public function getSingleArticleDet($id) {
        try {
            $checkArticleIdExists = NewsArticles::getFirstData(['id' => $id]);

            if(!$checkArticleIdExists) {
                return response([
                        'error' => true,
                        'message' => 'Article not found. The requested article ID does not exist.'
                ],404);
            }

            $getArticleDet = NewsArticles::getFirstData(['id' => $id]);

            return response([
                'error' => false,
                'message' => 'Articles details fetched successfully.',
                'data' => $getArticleDet
            ],200);

        } catch(\Exception $e) {
            return response([
                'error'=>true,
                'message'=> $e->getMessage()                 
            ], 500);            
        }
    }
}
