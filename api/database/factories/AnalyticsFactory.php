<?php

namespace Database\Factories;

use App\Models\Analytics;
use Illuminate\Database\Eloquent\Factories\Factory;

class AnalyticsFactory extends Factory
{
    protected $model = Analytics::class;

    public function definition(): array
    {
        return [
            'total_sales' => fake()->randomFloat(2, 1000, 50000),
            'total_orders' => fake()->numberBetween(10, 200),
            'total_customers' => fake()->numberBetween(5, 50),
            'date' => fake()->date('Y-m-d', 'now'),
        ];
    }
}
