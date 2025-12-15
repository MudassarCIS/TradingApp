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
            if (!Schema::hasColumn('users', 'api_token')) {
                if (Schema::hasColumn('users', 'remember_token')) {
                    $table->text('api_token')->nullable()->after('remember_token');
                } else {
                    $table->text('api_token')->nullable();
                }
            }
            if (!Schema::hasColumn('users', 'refresh_token')) {
                if (Schema::hasColumn('users', 'api_token')) {
                    $table->text('refresh_token')->nullable()->after('api_token');
                } else {
                    $table->text('refresh_token')->nullable();
                }
            }
            if (!Schema::hasColumn('users', 'api_password')) {
                if (Schema::hasColumn('users', 'refresh_token')) {
                    $table->text('api_password')->nullable()->after('refresh_token'); // Encrypted password for API login
                } else {
                    $table->text('api_password')->nullable(); // Encrypted password for API login
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'api_token')) {
                $table->dropColumn('api_token');
            }
            if (Schema::hasColumn('users', 'refresh_token')) {
                $table->dropColumn('refresh_token');
            }
            if (Schema::hasColumn('users', 'api_password')) {
                $table->dropColumn('api_password');
            }
        });
    }
};
