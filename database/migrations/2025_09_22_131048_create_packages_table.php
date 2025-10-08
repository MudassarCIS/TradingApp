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
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 20, 8);
            $table->string('currency', 10)->default('USDT');
            $table->decimal('min_investment', 20, 8);
            $table->decimal('max_investment', 20, 8)->nullable();
            $table->decimal('daily_return_rate', 5, 4)->default(0); // percentage
            $table->integer('duration_days')->default(30);
            $table->decimal('profit_share', 5, 2)->default(50); // admin takes 50%
            $table->boolean('is_active')->default(true);
            $table->json('features')->nullable(); // package features
            $table->timestamps();
            
            $table->index(['is_active', 'price']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('packages');
    }
};
