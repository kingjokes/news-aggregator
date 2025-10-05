<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Source;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'General',
            'Business',
            'Technology',
            'Entertainment',
            'Health',
            'Science',
            'Sports',
            'Politics',
            'World',
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(
                ['slug' => Str::slug($category)],
                ['name' => $category]
            );
        }

        $sources = [
            ['name' => 'NewsAPI', 'slug' => 'newsapi'],
            ['name' => 'The Guardian', 'slug' => 'the-guardian'],
            ['name' => 'New York Times', 'slug' => 'new-york-times'],
        ];

        foreach ($sources as $source) {
            Source::firstOrCreate(
                ['slug' => $source['slug']],
                ['name' => $source['name']]
            );
        }
    }
}