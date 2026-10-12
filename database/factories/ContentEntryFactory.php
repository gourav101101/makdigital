<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ContentEntryFactory extends Factory
{
    public function definition(): array
    {
        return ['kind' => 'article', 'slug' => fake()->unique()->slug(), 'title' => fake()->sentence(), 'summary' => fake()->paragraph(), 'body' => fake()->paragraphs(3, true), 'published' => false, 'position' => 0, 'details' => []];
    }
}
