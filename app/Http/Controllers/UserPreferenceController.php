<?php

namespace App\Http\Controllers;

use App\Helper\Validation\PreferenceValidation;
use App\Models\Categories;
use App\Models\NewsArticles;
use App\Models\Sources;
use App\Models\UserAuthorPreferences;
use App\Models\UserCategoryPreferences;
use App\Models\UserSourcePreferences;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserPreferenceController extends Controller
{
    // Function to set user preference
    public function setUserPreferences(Request $request) {
        $validator = PreferenceValidation::setPreference($request);

        if($validator->fails()){  // If validation fails, return the first error message
            return response([
                'error' => true,
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {

            $categories = [];
            $sources = [];

            // Handle preferred categories
            if ($request->has('preferred_category')) {
                foreach ($request->preferred_category as $categoryName) {
                    $category = Categories::getFirstData(['category' => $categoryName]);
                    if ($category) {
                        $categories[] = $category->id;
                    }
                }

                // Step 2: Insert or update new preferred categories
                foreach ($categories as $categoryId) {
                    UserCategoryPreferences::updateOrInsert(
                        ['user_id' => auth()->user()->id, 'category_id' => $categoryId],
                        ['user_id' => auth()->user()->id, 'category_id' => $categoryId]
                    );
                }
            }

            if ($request->has('preferred_source')) {
                foreach ($request->preferred_source as $sourceName) {
                    $source = Sources::getFirstData(['source' =>  $sourceName]);
                    if ($source) {
                        $sources[] = $source->id;
                    }
                }

              

                // Step 2: Insert or update new preferred categories
                foreach ($sources as $sourceId) {
                    UserSourcePreferences::updateOrInsert(
                        ['user_id' => auth()->user()->id, 'source_id' => $sourceId],
                        ['user_id' => auth()->user()->id, 'source_id' => $sourceId]
                    );
                }
            }

            if ($request->has('preferred_author')) {
                foreach ($request->preferred_author as $author) {
                    $authorExists = NewsArticles::where('author', $author)->exists();
                    if ($authorExists) {
                        // If the author exists, proceed to insert or update the preference
                        UserAuthorPreferences::updateOrInsert(
                            ['user_id' => auth()->user()->id, 'author' => $author], 
                            ['user_id' => auth()->user()->id, 'author' => $author]  
                        );
                    } else {
                        return response([
                            'error' => true,
                            'message' => 'The selected preferred author is invalid.'
                        ],422);
                    }
                }
            }
            
            return response([
                'error'=> false,
                'message'=> 'User preferences set successfully.'                 
            ], 200);
        } catch(\Exception $e) {
            return response([
                'error'=>true,
                'message'=> $e->getMessage()                 
            ], 500);
        }  
    }

    // Function to view user preferences
    public function getUserPreferences(Request $request) {
        try {
            // Retrieve the authenticated user's ID
            $userId = Auth::user()->id;
        
            // Fetch user category preferences from the UserCategoryPreferences model
            $categories = UserCategoryPreferences::getData(['user_id' => $userId]);

            // Fetch user source preferences from the UserSourcePreferences model
            $sources = UserSourcePreferences::getData(['user_id' => $userId]);

            // Fetch user author preferences
            $authors = UserAuthorPreferences::getData(['user_id' => $userId]);

           // Return the structured response
            return response()->json([
                'error' => false,
                'message' => 'User preferences fetched successfully.',
                'data' => [
                    'categories' => $categories,        // Categories preferences data  
                    'sources' => $sources,               // Sources preferences data
                    'authors' => $authors
                ]
            ], 200);

        } catch (\Throwable $e) {
            return response([
                'error'=>true,
                'message'=> $e->getMessage()                 
            ], 500);
        }
    }

    // Function to get user personalized news feeds
    public function getUserPreferredNews() {
        try {
            // Get the authenticated user
            $userId = Auth::user()->id;

            // Fetch user category preferences and pluck the category names
            $categories = UserCategoryPreferences::getData(['user_id' => $userId])->pluck('category');

            // Fetch user source preferences and pluck the source names
            $sources = UserSourcePreferences::getData(['user_id' => $userId])->pluck('source');

            $authors = UserAuthorPreferences::getData(['user_id' => $userId])->pluck('author');

            // Ensure categories and sources are not empty before passing to the query
            if ($categories->isEmpty() && $sources->isEmpty() && $authors->isEmpty()) {
                return response([
                    'error' => true,
                    'message' => 'No categories or sources or authors found for this user.'
                ], 400);
            }

            // Fetch personalized news feed based on user preferences
            $newsArticles = NewsArticles::getNewsFeed($categories, $sources, $authors);

             // Check if there are no news articles
            if ($newsArticles->isEmpty()) {
                return response([
                    'error' => false,
                    'message' => 'No news found based on your preferences.'
                ], 404);
            }

            // Return the structured response
            return response([
                'error' => false,
                'message' => 'User preferred news fetched successfully.',
                'data' => $newsArticles
            ], 200);
        } catch (\Throwable $e) {
            // Return error response if something goes wrong
            return response()->json([
                'error' => true,
                'message' => $e->getMessage()
            ], 500);
        }
    }

   // Function to get the list of authors
    public function getAuthorList() {
        try {
            // Fetch the list of authors from NewsArticles model
            $getAuthorList = NewsArticles::getData();

            // Check if the data array is empty
            if ($getAuthorList->isEmpty()) {
                return response([
                    'error' => true,
                    'message' => "No authors found.", 
                ], 404); // HTTP status code 200 for success
            }
            // Return the response with a success message and the retrieved data
            return response()->json([
                'error' => false, 
                'message' => "Authors fetched successfully.", 
                'data' => $getAuthorList 
            ], 200); 
        } catch (\Throwable $e) {
            // Catch any unexpected errors and return an error response
            return response()->json([
                'error' => true, 
                'message' => $e->getMessage() 
            ], 500); // HTTP status code 500 for server error
        }
    }

    
}
