<?php

namespace Database\Factories;

use App\Models\Source;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class SourceFactory extends Factory
{
    protected $model = Source::class;

    public function definition(): array
    {
        $name = fake()->company() . ' News';
        
        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'api_identifier' => Str::slug($name),
        ];
    }
}