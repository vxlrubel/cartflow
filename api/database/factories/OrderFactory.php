<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'user_id' => User::inRandomOrder()->first()?->id,
            'total_amount' => fake()->randomFloat(2, 50, 2000),
            'status' => fake()->randomElement(['pending', 'paid', 'shipped', 'delivered', 'cancelled']),
            'payment_status' => fake()->randomElement(['pending', 'paid', 'failed', 'refunded']),
        ];
    }
}
