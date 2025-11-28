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
        Schema::table('customers_wallets', function (Blueprint $table) {
            $table->unsignedBigInteger('caused_by_user_id')->nullable()->after('related_id')->comment('User ID who caused this bonus (investor or profit earner)');
            $table->foreign('caused_by_user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers_wallets', function (Blueprint $table) {
            $table->dropForeign(['caused_by_user_id']);
            $table->dropColumn('caused_by_user_id');
        });
    }
};

