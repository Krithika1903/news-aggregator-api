<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserCategoryPreferences extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category_id',
    ];

    public static function deleteData($condition) {
        return self::where('user_id', $condition['user_id'])
            ->whereNotIn('category_id', $condition['category_id'])  
            ->delete();
    }

    public static function getData($condition) {
        $result = self::select('user_id','category_id','categories.category')
            ->leftJoin('categories', 'categories.id','=','user_category_preferences.category_id')
            ->where($condition);
        return $result->get();
    }
}
