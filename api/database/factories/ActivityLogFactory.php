<?php

namespace Database\Factories;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ActivityLogFactory extends Factory
{
    protected $model = ActivityLog::class;

    public function definition(): array
    {
        return [
            'user_id' => User::inRandomOrder()->first()?->id,
            'action' => fake()->randomElement(['created', 'updated', 'deleted', 'viewed', 'logged_in', 'logged_out']),
            'description' => fake()->sentence(),
            'subject_type' => fake()->randomElement(['App\Models\Product', 'App\Models\Order', 'App\Models\User', 'App\Models\Category']),
            'subject_id' => fake()->numberBetween(1, 50),
        ];
    }
}
