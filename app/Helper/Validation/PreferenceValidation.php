<?php
namespace App\Helper\Validation;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules;

class PreferenceValidation
{
    // User registration validation rules
    public static function setPreference(Request $request) {
        $rules = [
            'preferred_category' => ['sometimes', 'array', 'exists:categories,category'], // Check only if provided
            'preferred_source' => ['sometimes', 'array', 'exists:sources,source'],     // Check only if provided
            'preferred_author' => ['sometimes', 'array'],                          // Check only if provided
        ];
    
        return Validator::make($request->all(), $rules);
    }
}