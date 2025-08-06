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
        // Remove 'name' from conversations if it exists
        if (Schema::hasColumn('conversations', 'name')) {
            Schema::table('conversations', function (Blueprint $table) {
                $table->dropColumn('name');
            });
        }
        // Remove 'is_admin' from conversation_user if it exists
        if (Schema::hasColumn('conversation_user', 'is_admin')) {
            Schema::table('conversation_user', function (Blueprint $table) {
                $table->dropColumn('is_admin');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Add 'name' back to conversations
        Schema::table('conversations', function (Blueprint $table) {
            $table->string('name')->nullable();
        });
        // Add 'is_admin' back to conversation_user
        Schema::table('conversation_user', function (Blueprint $table) {
            $table->boolean('is_admin')->default(false);
        });
    }
};
