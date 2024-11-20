<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('news_articles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title', 500); 
            $table->text('description')->nullable();
            $table->string('source')->nullable();
            $table->string('author')->nullable();
            $table->string('category', 220)->nullable();
            $table->text('url');
            $table->dateTime('published_at')->nullable();
            $table->timestamps();
        
            // Adding indexes
            $table->index('title'); // Index without specifying length, as 'title' is now a string
            $table->index('source'); // For filtering by source
            $table->index('category'); // For filtering by category
            $table->index('author'); // For filtering by author
            $table->index('published_at'); // For sorting/filtering by date
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('news_articles');
    }
};
