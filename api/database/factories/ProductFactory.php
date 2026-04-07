<?php

namespace Database\Factories;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => fake()->paragraph(),
            'price' => fake()->randomFloat(2, 10, 1000),
            'sale_price' => null,
            'category_id' => Category::inRandomOrder()->first()?->id,
            'brand_id' => Brand::inRandomOrder()->first()?->id,
            'stock' => fake()->numberBetween(0, 100),
            'sku' => fake()->unique()->uuid(),
            'status' => fake()->randomElement(['active', 'inactive']),
        ];
    }

    public function onSale(): static
    {
        return $this->state(fn (array $attributes) => [
            'sale_price' => fake()->randomFloat(2, 5, 500),
        ]);
    }
}
