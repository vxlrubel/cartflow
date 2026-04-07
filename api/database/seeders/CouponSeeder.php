<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Coupon;
use App\Models\Product;
use Illuminate\Database\Seeder;

class CouponSeeder extends Seeder
{
    public function run(): void
    {
        $coupons = [
            ['code' => 'SAVE10', 'type' => 'cart', 'discount_type' => 'percentage', 'discount_value' => 10],
            ['code' => 'SAVE20', 'type' => 'cart', 'discount_type' => 'percentage', 'discount_value' => 20],
            ['code' => 'FLAT50', 'type' => 'cart', 'discount_type' => 'fixed', 'discount_value' => 50],
            ['code' => 'ELECTRONIC15', 'type' => 'category', 'discount_type' => 'percentage', 'discount_value' => 15],
            ['code' => 'CLOTHING25', 'type' => 'category', 'discount_type' => 'percentage', 'discount_value' => 25],
            ['code' => 'PRODUCT30', 'type' => 'product', 'discount_type' => 'percentage', 'discount_value' => 30],
            ['code' => 'WELCOME100', 'type' => 'cart', 'discount_type' => 'fixed', 'discount_value' => 100],
            ['code' => 'HOLIDAY50', 'type' => 'cart', 'discount_type' => 'percentage', 'discount_value' => 50],
            ['code' => 'NEWUSER20', 'type' => 'cart', 'discount_type' => 'percentage', 'discount_value' => 20],
            ['code' => 'BUNDLE15', 'type' => 'category', 'discount_type' => 'percentage', 'discount_value' => 15],
            ['code' => 'TECH10', 'type' => 'product', 'discount_type' => 'percentage', 'discount_value' => 10],
            ['code' => 'HOME25', 'type' => 'category', 'discount_type' => 'percentage', 'discount_value' => 25],
            ['code' => 'SPORTS30', 'type' => 'category', 'discount_type' => 'percentage', 'discount_value' => 30],
            ['code' => 'BOOKS15', 'type' => 'product', 'discount_type' => 'percentage', 'discount_value' => 15],
            ['code' => 'BEAUTY20', 'type' => 'category', 'discount_type' => 'percentage', 'discount_value' => 20],
            ['code' => 'FOOD10', 'type' => 'cart', 'discount_type' => 'percentage', 'discount_value' => 10],
            ['code' => 'GROCERY15', 'type' => 'category', 'discount_type' => 'percentage', 'discount_value' => 15],
            ['code' => 'FURNITURE25', 'type' => 'category', 'discount_type' => 'percentage', 'discount_value' => 25],
            ['code' => 'TOYS20', 'type' => 'category', 'discount_type' => 'percentage', 'discount_value' => 20],
            ['code' => 'PET10', 'type' => 'product', 'discount_type' => 'percentage', 'discount_value' => 10],
            ['code' => 'AUTO15', 'type' => 'category', 'discount_type' => 'percentage', 'discount_value' => 15],
            ['code' => 'OFFICE25', 'type' => 'category', 'discount_type' => 'percentage', 'discount_value' => 25],
            ['code' => 'JEWELRY30', 'type' => 'product', 'discount_type' => 'percentage', 'discount_value' => 30],
            ['code' => 'MUSIC10', 'type' => 'product', 'discount_type' => 'percentage', 'discount_value' => 10],
            ['code' => 'GAMING20', 'type' => 'category', 'discount_type' => 'percentage', 'discount_value' => 20],
            ['code' => 'BABY15', 'type' => 'category', 'discount_type' => 'percentage', 'discount_value' => 15],
            ['code' => 'OUTDOOR25', 'type' => 'category', 'discount_type' => 'percentage', 'discount_value' => 25],
            ['code' => 'PARTY10', 'type' => 'product', 'discount_type' => 'percentage', 'discount_value' => 10],
            ['code' => 'LUGGAGE20', 'type' => 'category', 'discount_type' => 'percentage', 'discount_value' => 20],
            ['code' => 'WATCHES15', 'type' => 'product', 'discount_type' => 'percentage', 'discount_value' => 15],
            ['code' => 'CAMERA25', 'type' => 'category', 'discount_type' => 'percentage', 'discount_value' => 25],
            ['code' => 'PHONE30', 'type' => 'product', 'discount_type' => 'percentage', 'discount_value' => 30],
            ['code' => 'COMPUTER10', 'type' => 'category', 'discount_type' => 'percentage', 'discount_value' => 10],
            ['code' => 'TABLET20', 'type' => 'product', 'discount_type' => 'percentage', 'discount_value' => 20],
            ['code' => 'HEADPHONES15', 'type' => 'product', 'discount_type' => 'percentage', 'discount_value' => 15],
            ['code' => 'SPEAKER25', 'type' => 'product', 'discount_type' => 'percentage', 'discount_value' => 25],
            ['code' => 'SMARTWATCH30', 'type' => 'product', 'discount_type' => 'percentage', 'discount_value' => 30],
            ['code' => 'KITCHEN10', 'type' => 'category', 'discount_type' => 'percentage', 'discount_value' => 10],
            ['code' => 'APPLIANCE20', 'type' => 'category', 'discount_type' => 'percentage', 'discount_value' => 20],
            ['code' => 'CLEANING15', 'type' => 'product', 'discount_type' => 'percentage', 'discount_value' => 15],
            ['code' => 'STORAGE25', 'type' => 'category', 'discount_type' => 'percentage', 'discount_value' => 25],
            ['code' => 'DECOR30', 'type' => 'category', 'discount_type' => 'percentage', 'discount_value' => 30],
            ['code' => 'LIGHTING10', 'type' => 'category', 'discount_type' => 'percentage', 'discount_value' => 10],
            ['code' => 'GARDEN20', 'type' => 'category', 'discount_type' => 'percentage', 'discount_value' => 20],
            ['code' => 'FLOORING15', 'type' => 'category', 'discount_type' => 'percentage', 'discount_value' => 15],
            ['code' => 'WALLART25', 'type' => 'product', 'discount_type' => 'percentage', 'discount_value' => 25],
            ['code' => 'PLANTS30', 'type' => 'category', 'discount_type' => 'percentage', 'discount_value' => 30],
            ['code' => 'BEDDING10', 'type' => 'category', 'discount_type' => 'percentage', 'discount_value' => 10],
            ['code' => 'BATHROOM20', 'type' => 'category', 'discount_type' => 'percentage', 'discount_value' => 20],
            ['code' => 'LAUNDRY15', 'type' => 'category', 'discount_type' => 'percentage', 'discount_value' => 15],
        ];

        $categories = Category::all();
        $products = Product::all();

        foreach ($coupons as $index => $data) {
            $coupon = Coupon::updateOrCreate(
                ['code' => $data['code']],
                [
                    'type' => $data['type'],
                    'discount_type' => $data['discount_type'],
                    'discount_value' => $data['discount_value'],
                    'max_usage' => rand(50, 500),
                    'used_count' => rand(0, 50),
                    'expires_at' => now()->addDays(rand(7, 90)),
                ]
            );

            if ($coupon->type === 'product' && $products->isNotEmpty()) {
                $coupon->products()->sync($products->random(min(5, $products->count()))->pluck('id'));
            } elseif ($coupon->type === 'category' && $categories->isNotEmpty()) {
                $coupon->categories()->sync($categories->random(min(3, $categories->count()))->pluck('id'));
            }
        }
    }
}
