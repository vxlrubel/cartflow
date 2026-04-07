<?php

namespace Database\Seeders;

use App\Models\Analytics;
use Illuminate\Database\Seeder;

class AnalyticsSeeder extends Seeder
{
    public function run(): void
    {
        for ($i = 0; $i < 50; $i++) {
            Analytics::updateOrCreate(
                ['date' => now()->subDays($i)->toDateString()],
                [
                    'total_sales' => rand(1000, 50000),
                    'total_orders' => rand(10, 200),
                    'total_customers' => rand(5, 50),
                    'date' => now()->subDays($i)->toDateString(),
                ]
            );
        }
    }
}
