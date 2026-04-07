<?php

namespace Database\Factories;

use App\Models\Coupon;
use Illuminate\Database\Eloquent\Factories\Factory;

class CouponFactory extends Factory
{
    protected $model = Coupon::class;

    public function definition(): array
    {
        return [
            'code' => strtoupper(fake()->unique()->bothify('????####')),
            'type' => fake()->randomElement(['product', 'category', 'cart']),
            'discount_type' => fake()->randomElement(['fixed', 'percentage']),
            'discount_value' => fake()->randomFloat(2, 5, 50),
            'max_usage' => fake()->numberBetween(100, 1000),
            'used_count' => fake()->numberBetween(0, 50),
            'expires_at' => fake()->dateTimeBetween('now', '+3 months'),
        ];
    }
}
