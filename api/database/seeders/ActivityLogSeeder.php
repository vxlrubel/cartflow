<?php

namespace Database\Seeders;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Seeder;

class ActivityLogSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $actions = ['created', 'updated', 'deleted', 'viewed', 'logged_in', 'logged_out', 'purchased', 'reviewed'];
        $subjects = ['App\Models\Product', 'App\Models\Order', 'App\Models\User', 'App\Models\Category', 'App\Models\Brand', 'App\Models\Coupon'];

        for ($i = 0; $i < 50; $i++) {
            ActivityLog::updateOrCreate(
                ['id' => $i + 1],
                [
                    'user_id' => $users->random()->id,
                    'action' => $actions[array_rand($actions)],
                    'description' => 'User performed action on '.strtolower(basename($subjects[array_rand($subjects)])),
                    'subject_type' => $subjects[array_rand($subjects)],
                    'subject_id' => rand(1, 50),
                ]
            );
        }
    }
}
