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
        Schema::create('referrals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('referrer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('referred_id')->constrained('users')->onDelete('cascade');
            $table->decimal('commission_rate', 5, 2)->default(10); // percentage
            $table->decimal('total_commission', 20, 8)->default(0);
            $table->decimal('pending_commission', 20, 8)->default(0);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamp('joined_at');
            $table->timestamps();
            
            $table->unique(['referrer_id', 'referred_id']);
            $table->index(['referrer_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referrals');
    }
};
