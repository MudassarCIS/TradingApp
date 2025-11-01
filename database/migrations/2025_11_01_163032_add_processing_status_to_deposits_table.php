<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Modify enum to include 'processing' status
        DB::statement("ALTER TABLE deposits MODIFY COLUMN status ENUM('pending', 'processing', 'approved', 'rejected', 'cancelled') DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert enum back to previous values (without processing)
        DB::statement("ALTER TABLE deposits MODIFY COLUMN status ENUM('pending', 'approved', 'rejected', 'cancelled') DEFAULT 'pending'");
    }
};
