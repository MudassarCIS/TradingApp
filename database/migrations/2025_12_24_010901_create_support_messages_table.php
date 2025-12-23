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
        Schema::create('support_messages', function (Blueprint $table) {
            $table->id();
            $table->string('thread_id')->index(); // groups messages in a conversation (format: user_{user_id})
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // customer
            $table->foreignId('admin_id')->nullable()->constrained('users')->onDelete('set null'); // admin who replied
            $table->text('message'); // message content
            $table->enum('sender_type', ['customer', 'admin']); // who sent the message
            $table->boolean('is_read_by_customer')->default(false);
            $table->boolean('is_read_by_admin')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['thread_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index(['admin_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('support_messages');
    }
};
