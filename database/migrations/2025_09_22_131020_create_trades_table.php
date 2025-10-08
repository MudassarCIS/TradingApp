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
        Schema::create('trades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('agent_id')->nullable();
            $table->string('trade_id')->unique();
            $table->string('symbol', 20); // BTCUSDT, ETHUSDT, etc.
            $table->enum('side', ['buy', 'sell']);
            $table->enum('type', ['market', 'limit', 'stop']);
            $table->decimal('quantity', 20, 8);
            $table->decimal('price', 20, 8)->nullable(); // for limit orders
            $table->decimal('stop_price', 20, 8)->nullable(); // for stop orders
            $table->decimal('executed_quantity', 20, 8)->default(0);
            $table->decimal('average_price', 20, 8)->nullable();
            $table->decimal('commission', 20, 8)->default(0);
            $table->enum('status', ['pending', 'partially_filled', 'filled', 'cancelled', 'rejected']);
            $table->enum('time_in_force', ['GTC', 'IOC', 'FOK'])->default('GTC');
            $table->decimal('profit_loss', 20, 8)->default(0);
            $table->decimal('profit_loss_percentage', 8, 4)->default(0);
            $table->string('exchange', 20); // binance, bingx
            $table->string('exchange_order_id')->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'status']);
            $table->index(['symbol', 'created_at']);
            $table->index(['exchange', 'exchange_order_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trades');
    }
};
