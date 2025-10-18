<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers_wallets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('currency', 10)->default('USDT');
            $table->decimal('amount', 20, 8);
            $table->string('payment_type', 50); // investment, bonus, trade_profit, trade_loss, withdraw
            $table->enum('transaction_type', ['debit', 'credit']);
            $table->unsignedBigInteger('related_id')->nullable()->comment('deposit id, bonus_wallet id, trade id etc');
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers_wallets');
    }
};
