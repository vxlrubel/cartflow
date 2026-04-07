<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'order_id' => Order::inRandomOrder()->first()?->id,
            'method' => fake()->randomElement(['credit_card', 'paypal', 'bank_transfer', 'cash']),
            'transaction_id' => fake()->uuid(),
            'status' => fake()->randomElement(['pending', 'success', 'failed']),
        ];
    }
}
