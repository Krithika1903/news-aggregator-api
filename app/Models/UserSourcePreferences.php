<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSourcePreferences extends Model
{
    use HasFactory;

    public static function getData($condition) {
        $result = self::select('user_id','source_id','sources.source')
            ->leftJoin('sources', 'sources.id','=','user_source_preferences.source_id')
            ->where($condition);

        return $result->get();

    }
}
