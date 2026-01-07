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
        Schema::table('trade_credentials', function (Blueprint $table) {
            if (!Schema::hasColumn('trade_credentials', 'connector_name')) {
                $table->string('connector_name')->nullable()->after('connector_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trade_credentials', function (Blueprint $table) {
            if (Schema::hasColumn('trade_credentials', 'connector_name')) {
                $table->dropColumn('connector_name');
            }
        });
    }
};
