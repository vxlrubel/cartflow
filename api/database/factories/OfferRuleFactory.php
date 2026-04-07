<?php

namespace Database\Factories;

use App\Models\Offer;
use App\Models\OfferRule;
use Illuminate\Database\Eloquent\Factories\Factory;

class OfferRuleFactory extends Factory
{
    protected $model = OfferRule::class;

    public function definition(): array
    {
        return [
            'offer_id' => Offer::inRandomOrder()->first()?->id,
            'rule_type' => fake()->randomElement(['buy_x_get_y', 'min_amount', 'min_quantity', 'percentage_discount']),
            'conditions' => json_encode([
                'min_amount' => fake()->randomFloat(2, 100, 500),
                'discount_percentage' => fake()->numberBetween(10, 50),
            ]),
        ];
    }
}
