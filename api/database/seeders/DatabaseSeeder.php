<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            PermissionSeeder::class,
            UserSeeder::class,
            CategorySeeder::class,
            BrandSeeder::class,
            ProductSeeder::class,
            AttributeSeeder::class,
            AttributeValueSeeder::class,
            ProductVariationSeeder::class,
            ReviewSeeder::class,
            OrderSeeder::class,
            PaymentSeeder::class,
            CouponSeeder::class,
            OfferSeeder::class,
            WishlistSeeder::class,
            AddressSeeder::class,
            AnalyticsSeeder::class,
            ActivityLogSeeder::class,
            AuditSeeder::class,
        ]);
    }
}
