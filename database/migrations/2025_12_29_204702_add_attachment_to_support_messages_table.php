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
        if (!Schema::hasTable('support_messages')) {
            return; // Table doesn't exist, skip this migration
        }

        // Check and add columns if they don't exist
        if (!Schema::hasColumn('support_messages', 'attachment')) {
            Schema::table('support_messages', function (Blueprint $table) {
                $table->string('attachment')->nullable()->after('message');
            });
        }

        if (!Schema::hasColumn('support_messages', 'attachment_name')) {
        Schema::table('support_messages', function (Blueprint $table) {
                $table->string('attachment_name')->nullable()->after('attachment');
            });
        }

        if (!Schema::hasColumn('support_messages', 'attachment_type')) {
            Schema::table('support_messages', function (Blueprint $table) {
                $table->string('attachment_type')->nullable()->after('attachment_name'); // image, pdf, word
        });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('support_messages', function (Blueprint $table) {
            $table->dropColumn(['attachment', 'attachment_name', 'attachment_type']);
        });
    }
};
