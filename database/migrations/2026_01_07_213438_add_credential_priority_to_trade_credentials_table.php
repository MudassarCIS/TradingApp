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
            if (!Schema::hasColumn('trade_credentials', 'credential_priority')) {
                $table->enum('credential_priority', ['primary', 'secondary', 'none'])->default('none')->after('credential_type');
                $table->index('credential_priority');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trade_credentials', function (Blueprint $table) {
            if (Schema::hasColumn('trade_credentials', 'credential_priority')) {
                $table->dropIndex(['credential_priority']);
                $table->dropColumn('credential_priority');
            }
        });
    }
};
