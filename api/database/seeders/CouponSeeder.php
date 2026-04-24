<?php

namespace Database\Seeders;

use App\Models\Coupon;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Database\Seeder;

class CouponSeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::take(5)->get();
        $categories = Category::take(3)->get();

        $coupons = [
            [
                'code' => 'SUMMER2024',
                'type' => 'product',
                'discount_type' => 'percentage',
                'discount_value' => 15,
                'max_usage' => 100,
                'used_count' => 23,
                'expires_at' => now()->addMonths(2),
            ],
            [
                'code' => 'FLASH20',
                'type' => 'product',
                'discount_type' => 'percentage',
                'discount_value' => 20,
                'max_usage' => 500,
                'used_count' => 145,
                'expires_at' => now()->addDays(7),
            ],
            [
                'code' => 'ELECTRONICS10',
                'type' => 'category',
                'discount_type' => 'percentage',
                'discount_value' => 10,
                'max_usage' => 200,
                'used_count' => 56,
                'expires_at' => now()->addMonths(1),
            ],
            [
                'code' => 'SAVE50',
                'type' => 'category',
                'discount_type' => 'fixed',
                'discount_value' => 50,
                'max_usage' => 50,
                'used_count' => 12,
                'expires_at' => now()->addWeeks(2),
            ],
            [
                'code' => 'WELCOME20',
                'type' => 'cart',
                'discount_type' => 'percentage',
                'discount_value' => 20,
                'max_usage' => 1000,
                'used_count' => 234,
                'expires_at' => null,
            ],
            [
                'code' => 'FREESHIP',
                'type' => 'cart',
                'discount_type' => 'fixed',
                'discount_value' => 10,
                'max_usage' => 0,
                'used_count' => 89,
                'expires_at' => now()->addMonths(3),
            ],
            [
                'code' => 'BULK25',
                'type' => 'cart',
                'discount_type' => 'percentage',
                'discount_value' => 25,
                'max_usage' => 75,
                'used_count' => 31,
                'expires_at' => now()->addDays(14),
            ],
        ];

        foreach ($coupons as $couponData) {
            $coupon = Coupon::create($couponData);

            if ($couponData['type'] === 'product' && $products->isNotEmpty()) {
                $coupon->products()->sync($products->random(min(2, $products->count()))->pluck('id'));
            }

            if ($couponData['type'] === 'category' && $categories->isNotEmpty()) {
                $coupon->categories()->sync($categories->random(min(2, $categories->count()))->pluck('id'));
            }
        }
    }
}