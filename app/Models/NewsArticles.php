<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NewsArticles extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'source',
        'author',
        'category',
        'url',
        'published_at',
    ];


    public static function addData($data) {
        $result = self::create($data);
        return $result->id;
    }
}
