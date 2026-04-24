<?php

namespace Database\Seeders;

use App\Models\Offer;
use App\Models\OfferRule;
use Illuminate\Database\Seeder;

class OfferSeeder extends Seeder
{
    public function run(): void
    {
        $offers = [
            [
                'name' => 'Black Friday 2024 Special',
                'type' => 'black_friday',
                'start_date' => now()->subDays(5),
                'end_date' => now()->addDays(3),
                'status' => 'active',
                'rules' => [
                    ['rule_type' => 'percentage_discount', 'conditions' => ['discount_percentage' => 50, 'min_order_amount' => 100]],
                ],
            ],
            [
                'name' => 'Buy 2 Get 1 Free - Clothing',
                'type' => 'bxgy',
                'start_date' => now()->subDays(10),
                'end_date' => now()->addDays(20),
                'status' => 'active',
                'rules' => [
                    ['rule_type' => 'min_quantity', 'conditions' => ['buy_quantity' => 2, 'get_quantity' => 1, 'category_id' => null]],
                ],
            ],
            [
                'name' => 'Weekend Flash Sale',
                'type' => 'flash',
                'start_date' => now()->subDay(),
                'end_date' => now()->addDays(2),
                'status' => 'active',
                'rules' => [
                    ['rule_type' => 'flash_sale', 'conditions' => ['discount_percentage' => 30, 'max_quantity' => 50]],
                ],
            ],
            [
                'name' => 'New Year Discount',
                'type' => 'percentage',
                'start_date' => now()->addDays(30),
                'end_date' => now()->addDays(45),
                'status' => 'inactive',
                'rules' => [
                    ['rule_type' => 'discount_rule', 'conditions' => ['min_order_amount' => 200, 'discount_percentage' => 15]],
                ],
            ],
            [
                'name' => 'Loyalty Reward',
                'type' => 'percentage',
                'start_date' => now()->subDays(30),
                'end_date' => now()->addMonths(6),
                'status' => 'active',
                'rules' => [
                    ['rule_type' => 'tiered', 'conditions' => ['tier_1_amount' => 500, 'tier_1_discount' => 5]],
                ],
            ],
        ];

        foreach ($offers as $offerData) {
            $rules = $offerData['rules'];
            unset($offerData['rules']);

            $offer = Offer::create($offerData);

            foreach ($rules as $rule) {
                $offer->rules()->create($rule);
            }
        }
    }
}