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

    public static function getArticles($filters,$limit) {
        // Start building the query to fetch articles with specific columns
        $result = self::select('id as article_id','title','description','source','author','category','published_at');
        
        // Apply filters based on the request parameters
        if (isset($filters['keyword'])) {
            $result = $result->where(function($query) use ($filters) {
                // Use the index on 'title' and 'description' for keyword search
                $query->where('title', 'like', '%' . $filters['keyword'] . '%')
                      ->orWhere('description', 'like', '%' . $filters['keyword'] . '%');
            });
        }
        
        if (isset($filters['category'])) {
            // Utilize the index on 'category' for filtering
            $result = $result->where('category', $filters['category']);
        }
        
        if (isset($filters['date'])) {
            // Utilize the index on 'published_at' for date-based filtering
            $result = $result->whereDate('published_at', '=', $filters['date']);
        }
    
        if (isset($filters['source'])) {
            // Utilize the index on 'source' for filtering by source
            $result = $result->where('source', $filters['source']);
        }

        // Pagination with query string
        $result = $result->paginate($limit)
                ->withQueryString()
                ->toArray();
        return $result;
    }

    public static function getFirstData($condition) {
        return self::where($condition)->first();
    }
}
