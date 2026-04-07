<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\User;
use Illuminate\Database\Seeder;

class AddressSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $cities = ['New York', 'Los Angeles', 'Chicago', 'Houston', 'Phoenix', 'Philadelphia', 'San Antonio', 'San Diego', 'Dallas', 'San Jose'];
        $countries = ['USA', 'Canada', 'UK', 'Germany', 'France', 'Japan', 'Australia', 'Brazil', 'India', 'China'];

        foreach ($users as $user) {
            $count = rand(1, 3);
            for ($i = 0; $i < $count; $i++) {
                Address::updateOrCreate(
                    ['user_id' => $user->id, 'id' => $user->id * 10 + $i],
                    [
                        'user_id' => $user->id,
                        'address' => rand(100, 9999).' '.fake()->streetName().' '.fake()->streetSuffix(),
                        'city' => $cities[array_rand($cities)],
                        'country' => $countries[array_rand($countries)],
                    ]
                );
            }
        }
    }
}
