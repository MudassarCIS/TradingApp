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
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('phone')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('id_document_type')->nullable(); // passport, driver_license, national_id
            $table->string('id_document_number')->nullable();
            $table->string('id_document_front')->nullable(); // file path
            $table->string('id_document_back')->nullable(); // file path
            $table->enum('kyc_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('kyc_notes')->nullable();
            $table->string('transaction_password')->nullable(); // hashed
            $table->string('referral_code')->unique();
            $table->foreignId('referred_by')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};
