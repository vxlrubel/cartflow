<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Database\Seeder;

class WishlistSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $products = Product::where('status', 'active')->get();

        foreach ($users as $user) {
            $wishlist = Wishlist::updateOrCreate(
                ['user_id' => $user->id]
            );

            $wishlist->products()->sync(
                $products->random(min(rand(1, 8), $products->count()))->pluck('id')->toArray()
            );
        }
    }
}
