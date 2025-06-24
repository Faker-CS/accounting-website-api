<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('conversation_id');
            $table->unsignedBigInteger('sender_id');
            $table->text('body');
            $table->string('content_type')->default('text'); // text, image, file
            $table->json('attachments')->nullable(); // Store attachment metadata
            $table->boolean('seen')->default(false);
            $table->timestamp('seen_at')->nullable();
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('conversation_id')
                ->references('id')
                ->on('conversations')
                ->onDelete('cascade');

            $table->foreign('sender_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            // Indexes for better performance
            $table->index(['conversation_id', 'created_at']);
            $table->index(['conversation_id', 'seen']);
            $table->index(['sender_id', 'created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('messages');
    }
};
