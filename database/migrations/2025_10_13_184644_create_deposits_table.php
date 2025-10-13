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
        Schema::create('deposits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('deposit_id')->unique(); // Custom deposit ID for reference
            $table->decimal('amount', 20, 8);
            $table->string('currency', 10);
            $table->string('network', 20);
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->string('proof_image')->nullable(); // Path to uploaded proof image
            $table->text('notes')->nullable(); // Admin notes
            $table->text('rejection_reason')->nullable(); // Reason for rejection
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'status']);
            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deposits');
    }
};
