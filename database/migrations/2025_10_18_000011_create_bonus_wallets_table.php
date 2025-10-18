<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bonus_wallets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('deposit_id')->nullable();
            $table->decimal('investment_amount', 20, 8);
            $table->unsignedBigInteger('user_id'); // who invested
            $table->unsignedBigInteger('parent_id'); // who received bonus
            $table->tinyInteger('parent_level'); // 1,2,3
            $table->decimal('bonus_amount', 20, 8);
            $table->string('currency', 10)->default('USDT');
            $table->unsignedBigInteger('package_id')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'parent_id']);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('parent_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('package_id')->references('id')->on('plans')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bonus_wallets');
    }
};
