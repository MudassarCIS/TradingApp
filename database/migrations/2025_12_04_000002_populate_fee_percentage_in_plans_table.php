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
        // Calculate and populate fee_percentage for all existing plans
        DB::statement('
            UPDATE plans 
            SET fee_percentage = ROUND((joining_fee / investment_amount) * 100, 2)
            WHERE investment_amount > 0
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Set fee_percentage to null (we can't really reverse the calculation)
        DB::statement('UPDATE plans SET fee_percentage = NULL');
    }
};

