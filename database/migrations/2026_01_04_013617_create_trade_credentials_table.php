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
        Schema::create('trade_credentials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('account_name');
            $table->unsignedBigInteger('connector_id')->nullable();
            $table->string('api_key')->default(''); // public_key
            $table->string('secret_key')->default('');
            $table->boolean('active_credentials')->default(0);
            $table->timestamps();
            
            $table->index(['user_id', 'connector_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trade_credentials');
    }
};
