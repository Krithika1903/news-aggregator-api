<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['category' => 'Technology', 'created_at' => now(), 'updated_at' => now()],
            ['category' => 'Health', 'created_at' => now(), 'updated_at' => now()],
            ['category' => 'Business', 'created_at' => now(), 'updated_at' => now()],
            ['category' => 'Entertainment', 'created_at' => now(), 'updated_at' => now()],
            ['category' => 'Sports', 'created_at' => now(), 'updated_at' => now()],
            ['category' => 'Science', 'created_at' => now(), 'updated_at' => now()],
            ['category' => 'General', 'created_at' => now(), 'updated_at' => now()],
        ];

        DB::table('categories')->insert($categories);
    }
}
