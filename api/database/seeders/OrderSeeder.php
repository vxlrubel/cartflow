<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $products = Product::where('status', 'active')->get();

        $statuses = ['pending', 'completed', 'cancelled', 'return'];
        $paymentStatuses = ['pending', 'paid', 'unpaid', 'refunded'];

        for ($i = 1; $i <= 50; $i++) {
            $user = $users->random();
            $orderItems = $products->random(rand(1, 5));
            $totalAmount = 0;

            $order = Order::updateOrCreate(
                ['id' => $i],
                [
                    'user_id' => $user->id,
                    'order_number' => 'ORD-' . strtoupper(uniqid()),
                    'total_amount' => 0,
                    'status' => $statuses[array_rand($statuses)],
                    'payment_status' => $paymentStatuses[array_rand($paymentStatuses)],
                ]
            );

            foreach ($orderItems as $product) {
                $quantity = rand(1, 3);
                $price = $product->sale_price ?? $product->price;
                $totalAmount += $price * $quantity;

                OrderItem::updateOrCreate(
                    ['order_id' => $order->id, 'product_id' => $product->id],
                    [
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'quantity' => $quantity,
                        'price' => $price,
                    ]
                );
            }

            $order->update(['total_amount' => $totalAmount]);
        }
    }
}
