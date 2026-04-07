<?php

namespace Database\Factories;

use App\Models\Audit;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AuditFactory extends Factory
{
    protected $model = Audit::class;

    public function definition(): array
    {
        return [
            'user_id' => User::inRandomOrder()->first()?->id,
            'table_name' => fake()->randomElement(['users', 'products', 'orders', 'categories', 'brands']),
            'record_id' => fake()->numberBetween(1, 50),
            'old_values' => json_encode(['name' => fake()->word()]),
            'new_values' => json_encode(['name' => fake()->word()]),
        ];
    }
}
