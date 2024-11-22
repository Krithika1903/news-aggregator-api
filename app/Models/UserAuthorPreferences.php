<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserAuthorPreferences extends Model
{
    use HasFactory;

    public static function getData($condition) {
        $result = self::select('user_id','author')
            ->where($condition);

        return $result->get();

    }
}
