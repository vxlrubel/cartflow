<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::where('name', 'admin')->first();
        $managerRole = Role::where('name', 'manager')->first();
        $customerRole = Role::where('name', 'customer')->first();

        $users = [
            ['name' => 'Admin User', 'email' => 'admin@cartflow.com', 'role_id' => $adminRole?->id],
            ['name' => 'Manager User', 'email' => 'manager@cartflow.com', 'role_id' => $managerRole?->id],
            ['name' => 'John Customer', 'email' => 'john@example.com', 'role_id' => $customerRole?->id],
            ['name' => 'Jane Customer', 'email' => 'jane@example.com', 'role_id' => $customerRole?->id],
            ['name' => 'Bob Wilson', 'email' => 'bob@example.com', 'role_id' => $customerRole?->id],
            ['name' => 'Alice Brown', 'email' => 'alice@example.com', 'role_id' => $customerRole?->id],
            ['name' => 'Charlie Davis', 'email' => 'charlie@example.com', 'role_id' => $customerRole?->id],
            ['name' => 'Diana Evans', 'email' => 'diana@example.com', 'role_id' => $customerRole?->id],
            ['name' => 'Edward Frank', 'email' => 'edward@example.com', 'role_id' => $customerRole?->id],
            ['name' => 'Fiona Garcia', 'email' => 'fiona@example.com', 'role_id' => $customerRole?->id],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['email' => $user['email']],
                [
                    'name' => $user['name'],
                    'password' => Hash::make('password'),
                    'role_id' => $user['role_id'],
                ]
            );
        }
    }
}
