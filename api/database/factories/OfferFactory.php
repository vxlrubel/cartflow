<?php

namespace Database\Factories;

use App\Models\Offer;
use Illuminate\Database\Eloquent\Factories\Factory;

class OfferFactory extends Factory
{
    protected $model = Offer::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->sentence(3),
            'type' => fake()->randomElement(['bxgy', 'flash', 'percentage', 'black_friday']),
            'start_date' => fake()->dateTimeBetween('now', '+1 week'),
            'end_date' => fake()->dateTimeBetween('+1 week', '+1 month'),
            'status' => fake()->randomElement(['active', 'inactive']),
        ];
    }
}
