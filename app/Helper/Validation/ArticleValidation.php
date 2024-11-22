<?php
namespace App\Helper\Validation;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules;

class ArticleValidation
{
    // View articles validation rules
    public static function articleValidation(Request $request) {
        $rules = [
            'date' => ['date','nullable','date_format:Y-m-d'],
            'category' => ['string', 'nullable','in:Business,Entertainment,Health,Science,Sports,Technology,General'],
            'source' => ['string', 'nullable','in:NewsAPI,New York Times,The Guardian'],
            'keyword' => ['string','nullable']          
        ];
        
        return Validator::make($request->all(), $rules);
    }

}