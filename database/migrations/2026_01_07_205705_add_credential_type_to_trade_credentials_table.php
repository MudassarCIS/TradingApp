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
            if (!Schema::hasColumn('trade_credentials', 'credential_type')) {
                $table->string('credential_type')->default('NEXA')->after('user_id');
                $table->index('credential_type');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trade_credentials', function (Blueprint $table) {
            if (Schema::hasColumn('trade_credentials', 'credential_type')) {
                $table->dropIndex(['credential_type']);
                $table->dropColumn('credential_type');
            }
        });
    }
};
