<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::all();
        $users = User::all();

        if ($products->isEmpty()) {
            $this->command->info('No products found. Please run ProductSeeder first.');
            return;
        }

        $comments = [
            'Excellent product! Highly recommended.',
            'Very good quality, fast shipping.',
            'Not bad, but could be better.',
            'Great value for money!',
            'Exceeded my expectations.',
            'Good product, okay delivery time.',
            'Amazing quality! Will buy again.',
            'Decent product, works as described.',
            'Pretty good, would recommend.',
            'Very satisfied with this purchase.',
            'Quality could be better for the price.',
            'Great product! Love it.',
            'Works perfectly, very happy.',
            'Good buy, no complaints.',
            'Awesome! Fast delivery too.',
        ];

        $statuses = ['pending', 'approved', 'rejected'];

        foreach ($products as $product) {
            $numReviews = rand(0, 5);
            
            for ($i = 0; $i < $numReviews; $i++) {
                $user = $users->random();
                $rating = rand(1, 5);
                $status = $statuses[array_rand($statuses)];
                
                Review::create([
                    'product_id' => $product->id,
                    'user_id' => $user->id,
                    'rating' => $rating,
                    'comment' => $comments[array_rand($comments)],
                    'status' => $status,
                ]);
            }
        }

        $this->command->info('Reviews seeded successfully.');
    }
}