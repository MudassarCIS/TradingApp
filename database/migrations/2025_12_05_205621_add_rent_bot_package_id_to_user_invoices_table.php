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
        Schema::table('user_invoices', function (Blueprint $table) {
            $table->foreignId('rent_bot_package_id')->nullable()->after('plan_id')->constrained('rent_bot_packages')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_invoices', function (Blueprint $table) {
            $table->dropForeign(['rent_bot_package_id']);
            $table->dropColumn('rent_bot_package_id');
        });
    }
};
