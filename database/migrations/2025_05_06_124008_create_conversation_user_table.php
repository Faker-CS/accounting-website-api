<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('conversation_user', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('conversation_id');
            $table->unsignedBigInteger('user_id');
            $table->boolean('is_admin')->default(false); // For group conversations
            $table->timestamp('last_read_at')->nullable();
            $table->boolean('is_muted')->default(false);
            $table->timestamps();
            
            $table->foreign('conversation_id')
                  ->references('id')
                  ->on('conversations')
                  ->onDelete('cascade');
                  
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');

            // Unique constraint to prevent duplicate participants
            $table->unique(['conversation_id', 'user_id']);
            
            // Indexes for better performance
            $table->index(['conversation_id', 'last_read_at']);
            $table->index(['user_id', 'last_read_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('conversation_user');
    }
};
