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
        Schema::create('agents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('status', ['active', 'inactive', 'paused'])->default('active');
            $table->string('strategy', 50); // scalping, swing, trend_following, etc.
            $table->json('trading_rules'); // buy/sell conditions, stop loss, take profit
            $table->decimal('initial_balance', 20, 8)->default(0);
            $table->decimal('current_balance', 20, 8)->default(0);
            $table->decimal('total_profit', 20, 8)->default(0);
            $table->decimal('total_loss', 20, 8)->default(0);
            $table->decimal('win_rate', 5, 2)->default(0); // percentage
            $table->integer('total_trades')->default(0);
            $table->integer('winning_trades')->default(0);
            $table->integer('losing_trades')->default(0);
            $table->decimal('max_drawdown', 5, 2)->default(0); // percentage
            $table->decimal('risk_per_trade', 5, 2)->default(2); // percentage of balance
            $table->boolean('auto_trading')->default(true);
            $table->timestamp('last_trade_at')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agents');
    }
};
