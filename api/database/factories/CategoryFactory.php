<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->word(),
            'parent_id' => null,
        ];
    }

    public function withParent(): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => Category::inRandomOrder()->first()?->id,
        ]);
    }
}
