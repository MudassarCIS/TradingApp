<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('investment_amount', 10, 2);
            $table->decimal('joining_fee', 10, 2);
            $table->integer('bots_allowed');
            $table->integer('trades_per_day');
            $table->decimal('direct_bonus', 10, 2);
            $table->decimal('referral_level_1', 5, 2); // First level referral percentage
            $table->decimal('referral_level_2', 5, 2); // Second level referral percentage
            $table->decimal('referral_level_3', 5, 2); // Third level referral percentage
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
