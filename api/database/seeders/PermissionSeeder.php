<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'view_products', 'create_products', 'edit_products', 'delete_products',
            'view_orders', 'create_orders', 'edit_orders', 'delete_orders',
            'view_customers', 'create_customers', 'edit_customers', 'delete_customers',
            'view_coupons', 'create_coupons', 'edit_coupons', 'delete_coupons',
            'view_reports', 'manage_settings', 'manage_users',
        ];
        foreach ($permissions as $permission) {
            Permission::updateOrCreate(['name' => $permission]);
        }
    }
}
