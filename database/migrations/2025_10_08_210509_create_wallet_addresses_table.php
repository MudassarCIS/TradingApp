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
        Schema::create('wallet_addresses', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "Bitcoin", "USDT", "Ethereum"
            $table->string('symbol'); // e.g., "BTC", "USDT", "ETH"
            $table->string('wallet_address');
            $table->string('network')->nullable(); // e.g., "TRC20", "ERC20", "BEP20"
            $table->string('qr_code_image')->nullable(); // Path to QR code image
            $table->text('instructions')->nullable(); // Additional instructions for users
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wallet_addresses');
    }
};
