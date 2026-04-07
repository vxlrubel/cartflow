<?php

namespace Database\Seeders;

use App\Models\Audit;
use App\Models\User;
use Illuminate\Database\Seeder;

class AuditSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $tables = ['users', 'products', 'orders', 'categories', 'brands', 'coupons', 'offers', 'addresses'];

        for ($i = 0; $i < 50; $i++) {
            $table = $tables[array_rand($tables)];
            Audit::updateOrCreate(
                ['id' => $i + 1],
                [
                    'user_id' => $users->random()->id,
                    'table_name' => $table,
                    'record_id' => rand(1, 50),
                    'old_values' => json_encode(['name' => fake()->word(), 'status' => 'inactive']),
                    'new_values' => json_encode(['name' => fake()->word(), 'status' => 'active']),
                ]
            );
        }
    }
}
