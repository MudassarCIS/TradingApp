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
        Schema::create('rent_bot_packages', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('allowed_bots');
            $table->unsignedInteger('allowed_trades');
            $table->decimal('amount', 12, 2);
            $table->enum('validity', ['month', 'year']);
            $table->tinyInteger('status')->default(1); // 1 = active, 0 = disabled
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rent_bot_packages');
    }
};


