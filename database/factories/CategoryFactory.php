<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        $name = fake()->unique()->randomElement([
            'Technology',
            'Business',
            'Sports',
            'Entertainment',
            'Health',
            'Science',
            'Politics',
            'World',
            'Travel',
            'Food'
        ]);
        
        return [
            'name' => $name,
            'slug' => Str::slug($name),
        ];
    }
}
