<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('messages', function (Blueprint $table) {
            // First drop the foreign key constraint
            $table->dropForeign(['receiver_id']);

            // Then drop the column
            $table->dropColumn('receiver_id');
        });
    }

    public function down()
    {
        Schema::table('messages', function (Blueprint $table) {
            // For rollback - recreate the column and foreign key
            $table->unsignedBigInteger('receiver_id')->nullable();
            $table->foreign('receiver_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
        });
    }
};
