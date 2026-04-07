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
            ['name' => 'Summer Sale 2024', 'type' => 'percentage', 'status' => 'active'],
            ['name' => 'Buy 2 Get 1 Free', 'type' => 'bxgy', 'status' => 'active'],
            ['name' => 'Flash Sale Weekend', 'type' => 'flash', 'status' => 'active'],
            ['name' => 'Black Friday Special', 'type' => 'black_friday', 'status' => 'active'],
            ['name' => 'Back to School', 'type' => 'percentage', 'status' => 'active'],
            ['name' => 'Holiday Gift Set', 'type' => 'bxgy', 'status' => 'inactive'],
            ['name' => 'New Year Deal', 'type' => 'percentage', 'status' => 'inactive'],
            ['name' => 'Clearance Sale', 'type' => 'percentage', 'status' => 'active'],
            ['name' => 'Member Exclusive', 'type' => 'percentage', 'status' => 'active'],
            ['name' => 'Free Shipping Day', 'type' => 'percentage', 'status' => 'active'],
            ['name' => 'Electronics Week', 'type' => 'flash', 'status' => 'active'],
            ['name' => 'Fashion Month', 'type' => 'percentage', 'status' => 'active'],
            ['name' => 'Home Makeover', 'type' => 'percentage', 'status' => 'inactive'],
            ['name' => 'Sports Season', 'type' => 'bxgy', 'status' => 'active'],
            ['name' => 'Tech Tuesday', 'type' => 'flash', 'status' => 'active'],
            ['name' => 'Mother Day Special', 'type' => 'percentage', 'status' => 'inactive'],
            ['name' => 'Father Day Deal', 'type' => 'percentage', 'status' => 'inactive'],
            ['name' => 'Valentine Offer', 'type' => 'percentage', 'status' => 'inactive'],
            ['name' => 'Easter Sale', 'type' => 'percentage', 'status' => 'inactive'],
            ['name' => 'Halloween Special', 'type' => 'black_friday', 'status' => 'inactive'],
            ['name' => 'Cyber Monday', 'type' => 'black_friday', 'status' => 'active'],
            ['name' => 'Super Savings', 'type' => 'percentage', 'status' => 'active'],
            ['name' => 'Premium Collection', 'type' => 'percentage', 'status' => 'inactive'],
            ['name' => 'Budget Friendly', 'type' => 'percentage', 'status' => 'active'],
            ['name' => 'Bundle Deal', 'type' => 'bxgy', 'status' => 'active'],
            ['name' => 'Limited Time', 'type' => 'flash', 'status' => 'active'],
            ['name' => 'Exclusive Access', 'type' => 'percentage', 'status' => 'active'],
            ['name' => 'VIP Offer', 'type' => 'percentage', 'status' => 'inactive'],
            ['name' => 'First Purchase', 'type' => 'percentage', 'status' => 'active'],
            ['name' => 'Repeat Customer', 'type' => 'percentage', 'status' => 'active'],
            ['name' => 'Bulk Discount', 'type' => 'percentage', 'status' => 'active'],
            ['name' => 'Wholesale Deal', 'type' => 'percentage', 'status' => 'inactive'],
            ['name' => 'Season Finale', 'type' => 'percentage', 'status' => 'inactive'],
            ['name' => 'Anniversary Sale', 'type' => 'black_friday', 'status' => 'active'],
            ['name' => 'Launch Special', 'type' => 'flash', 'status' => 'active'],
            ['name' => 'Influencer Deal', 'type' => 'percentage', 'status' => 'active'],
            ['name' => 'Social Media Offer', 'type' => 'percentage', 'status' => 'inactive'],
            ['name' => 'Newsletter Signup', 'type' => 'percentage', 'status' => 'active'],
            ['name' => 'App Exclusive', 'type' => 'percentage', 'status' => 'active'],
            ['name' => 'Mobile Deal', 'type' => 'flash', 'status' => 'active'],
            ['name' => 'Desktop Bundle', 'type' => 'bxgy', 'status' => 'active'],
            ['name' => 'Gaming Package', 'type' => 'percentage', 'status' => 'active'],
            ['name' => 'Accessory Set', 'type' => 'bxgy', 'status' => 'active'],
            ['name' => 'Upgrade Offer', 'type' => 'percentage', 'status' => 'active'],
            ['name' => 'Trade In Deal', 'type' => 'percentage', 'status' => 'active'],
            ['name' => 'Referral Bonus', 'type' => 'percentage', 'status' => 'active'],
            ['name' => 'Loyalty Reward', 'type' => 'percentage', 'status' => 'active'],
            ['name' => 'Points Redemption', 'type' => 'percentage', 'status' => 'active'],
            ['name' => 'Gift Card Bonus', 'type' => 'percentage', 'status' => 'inactive'],
        ];

        $rules = [
            'min_amount' => ['type' => 'min_amount', 'min_amount' => 100, 'discount' => 10],
            'min_quantity' => ['type' => 'min_quantity', 'min_qty' => 2, 'free_qty' => 1],
            'percentage_discount' => ['type' => 'percentage', 'discount' => 25],
            'buy_x_get_y' => ['type' => 'buy_x_get_y', 'buy' => 2, 'get' => 1],
            'tiered_discount' => ['type' => 'tiered', 'tiers' => [[100, 10], [200, 15], [500, 20]]],
        ];

        foreach ($offers as $index => $data) {
            $startDate = now()->addDays(rand(-30, 30));
            $endDate = $startDate->copy()->addDays(rand(7, 60));

            $offer = Offer::updateOrCreate(
                ['id' => $index + 1],
                [
                    'name' => $data['name'],
                    'type' => $data['type'],
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'status' => $data['status'],
                ]
            );

            $ruleTypes = array_rand($rules, rand(1, 2));
            if (! is_array($ruleTypes)) {
                $ruleTypes = [$ruleTypes];
            }

            foreach ($ruleTypes as $type) {
                OfferRule::create([
                    'offer_id' => $offer->id,
                    'rule_type' => $type,
                    'conditions' => json_encode($rules[$type]),
                ]);
            }
        }
    }
}
