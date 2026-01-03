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
            $table->foreign('connector_id')->references('id')->on('connectors')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trade_credentials', function (Blueprint $table) {
            $table->dropForeign(['connector_id']);
        });
    }
};
