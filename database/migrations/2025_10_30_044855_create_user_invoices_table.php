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
        Schema::create('user_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('invoice_type'); // "Rent A Bot" or "Sharing Nexa"
            $table->decimal('amount', 10, 2); // Amount to be paid
            $table->date('due_date'); // Default: current_date + 7 days
            $table->string('status')->default('Unpaid'); // Default "Unpaid"
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_invoices');
    }
};
