<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('offers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['bxgy', 'flash', 'percentage', 'black_friday']);
            $table->timestamp('start_date');
            $table->timestamp('end_date');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('offer_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('offer_id')->constrained()->onDelete('cascade');
            $table->string('rule_type');
            $table->json('conditions');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offer_rules');
        Schema::dropIfExists('offers');
    }
};
