<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE orders MODIFY status VARCHAR(20) DEFAULT 'pending'");
        DB::statement("UPDATE orders SET status = 'completed' WHERE status IN ('delivered', 'shipped', 'paid')");
        DB::statement("ALTER TABLE orders MODIFY status ENUM('pending', 'completed', 'cancelled', 'return') DEFAULT 'pending'");
        
        DB::statement("ALTER TABLE orders MODIFY payment_status VARCHAR(20) DEFAULT 'unpaid'");
        DB::statement("UPDATE orders SET payment_status = 'paid' WHERE payment_status = 'success'");
        DB::statement("UPDATE orders SET payment_status = 'unpaid' WHERE payment_status = 'failed'");
        DB::statement("ALTER TABLE orders MODIFY payment_status ENUM('paid', 'unpaid', 'pending', 'refunded') DEFAULT 'unpaid'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE orders MODIFY status VARCHAR(20) DEFAULT 'pending'");
        DB::statement("ALTER TABLE orders MODIFY status ENUM('pending', 'paid', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending'");
        
        DB::statement("ALTER TABLE orders MODIFY payment_status VARCHAR(20) DEFAULT 'pending'");
        DB::statement("ALTER TABLE orders MODIFY payment_status ENUM('pending', 'paid', 'failed', 'refunded') DEFAULT 'pending'");
    }
};