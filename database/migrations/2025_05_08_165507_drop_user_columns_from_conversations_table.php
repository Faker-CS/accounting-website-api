<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('conversations', function (Blueprint $table) {
            // First drop foreign key constraints
            $table->dropForeign(['user_one_id']);
            $table->dropForeign(['user_two_id']);
            
            // Then drop the columns
            $table->dropColumn(['user_one_id', 'user_two_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('conversations', function (Blueprint $table) {
            // For rollback - recreate columns and foreign keys
            $table->unsignedBigInteger('user_one_id')->nullable();
            $table->unsignedBigInteger('user_two_id')->nullable();
            
            $table->foreign('user_one_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');
                  
            $table->foreign('user_two_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');
        });
    }
};
