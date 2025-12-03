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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'active_pex_plan_id')) {
                $table->unsignedBigInteger('active_pex_plan_id')->nullable()->after('active_plan_id');
                $table->foreign('active_pex_plan_id')->references('id')->on('rent_bot_packages')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'active_pex_plan_id')) {
                $table->dropForeign(['active_pex_plan_id']);
                $table->dropColumn('active_pex_plan_id');
            }
        });
    }
};
