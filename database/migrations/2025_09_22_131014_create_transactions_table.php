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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('transaction_id')->unique();
            $table->enum('type', ['deposit', 'withdrawal', 'transfer', 'trade_profit', 'trade_loss', 'commission', 'refund']);
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'cancelled'])->default('pending');
            $table->string('currency', 10);
            $table->decimal('amount', 20, 8);
            $table->decimal('fee', 20, 8)->default(0);
            $table->decimal('net_amount', 20, 8); // amount - fee
            $table->string('from_address')->nullable();
            $table->string('to_address')->nullable();
            $table->string('tx_hash')->nullable(); // blockchain transaction hash
            $table->integer('confirmations')->default(0);
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable(); // additional data
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'type']);
            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
