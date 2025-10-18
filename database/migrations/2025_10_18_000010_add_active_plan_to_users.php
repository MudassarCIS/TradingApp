<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'active_plan_id')) {
                $table->unsignedBigInteger('active_plan_id')->nullable()->after('referred_by');
                $table->decimal('active_investment_amount', 20, 8)->default(0)->after('active_plan_id');
                $table->foreign('active_plan_id')->references('id')->on('plans')->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'active_investment_amount')) {
                $table->dropColumn('active_investment_amount');
            }
            if (Schema::hasColumn('users', 'active_plan_id')) {
                $table->dropForeign(['active_plan_id']);
                $table->dropColumn('active_plan_id');
            }
        });
    }
};
