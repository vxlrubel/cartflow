<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Database\Seeder;

class PaymentSeeder extends Seeder
{
    public function run(): void
    {
        $orders = Order::all();
        $methods = ['credit_card', 'paypal', 'bank_transfer', 'cash', 'stripe'];
        $statuses = ['pending', 'success', 'failed'];

        foreach ($orders as $order) {
            Payment::updateOrCreate(
                ['order_id' => $order->id],
                [
                    'order_id' => $order->id,
                    'method' => $methods[array_rand($methods)],
                    'transaction_id' => 'TXN-'.strtoupper(bin2hex(random_bytes(8))),
                    'status' => $statuses[array_rand($statuses)],
                ]
            );
        }
    }
}
