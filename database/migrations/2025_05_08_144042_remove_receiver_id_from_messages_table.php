<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        // Check if the foreign key exists and drop it using pure SQL
        $fkName = null;
        $dbName = DB::getDatabaseName();
        $result = DB::select(
            "SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'messages' AND COLUMN_NAME = 'receiver_id' AND REFERENCED_COLUMN_NAME IS NOT NULL",
            [$dbName]
        );
        if (!empty($result)) {
            $fkName = $result[0]->CONSTRAINT_NAME;
            DB::statement("ALTER TABLE messages DROP FOREIGN KEY `$fkName`");
        }

        // Drop the column if it exists
        if (Schema::hasColumn('messages', 'receiver_id')) {
            Schema::table('messages', function (Blueprint $table) {
                $table->dropColumn('receiver_id');
            });
        }
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
