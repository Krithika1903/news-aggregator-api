<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SourceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['source' => 'New York Times', 'created_at' => now(), 'updated_at' => now()],
            ['source' => 'The Guardian', 'created_at' => now(), 'updated_at' => now()],
            ['source' => 'NewsAPI', 'created_at' => now(), 'updated_at' => now()]
        ];

        DB::table('sources')->insert($categories);
    }
}
