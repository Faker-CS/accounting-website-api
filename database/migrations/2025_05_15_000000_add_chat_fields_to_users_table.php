<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('status')->default('offline'); // online, offline, away
            $table->timestamp('last_seen_at')->nullable();
            $table->string('avatar_url')->nullable();
            $table->boolean('is_typing')->default(false);
            $table->unsignedBigInteger('current_conversation_id')->nullable();
            
            $table->foreign('current_conversation_id')
                  ->references('id')
                  ->on('conversations')
                  ->onDelete('set null');
                  
            // Indexes for better performance
            $table->index('status');
            $table->index('last_seen_at');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['current_conversation_id']);
            $table->dropColumn([
                'status',
                'last_seen_at',
                'avatar_url',
                'is_typing',
                'current_conversation_id'
            ]);
        });
    }
}; 