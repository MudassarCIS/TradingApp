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
        Schema::dropIfExists('api_accounts');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('api_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('exchange', 20);
            $table->string('api_key');
            $table->string('secret_key');
            $table->string('passphrase')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_verified')->default(false);
            $table->json('permissions')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();
            
            $table->unique(['user_id', 'exchange']);
            $table->index(['exchange', 'is_active']);
        });
    }
};
