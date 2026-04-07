<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductVariation;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductVariationFactory extends Factory
{
    protected $model = ProductVariation::class;

    public function definition(): array
    {
        return [
            'product_id' => Product::inRandomOrder()->first()?->id,
            'sku' => fake()->unique()->uuid(),
            'price' => fake()->randomFloat(2, 10, 500),
            'stock' => fake()->numberBetween(0, 50),
        ];
    }
}
