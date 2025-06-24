<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        // Check and drop foreign key for user_one_id
        $dbName = DB::getDatabaseName();
        $result1 = DB::select(
            "SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'conversations' AND COLUMN_NAME = 'user_one_id' AND REFERENCED_COLUMN_NAME IS NOT NULL",
            [$dbName]
        );
        if (!empty($result1)) {
            $fkName1 = $result1[0]->CONSTRAINT_NAME;
            DB::statement("ALTER TABLE conversations DROP FOREIGN KEY `$fkName1`");
        }

        // Check and drop foreign key for user_two_id
        $result2 = DB::select(
            "SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'conversations' AND COLUMN_NAME = 'user_two_id' AND REFERENCED_COLUMN_NAME IS NOT NULL",
            [$dbName]
        );
        if (!empty($result2)) {
            $fkName2 = $result2[0]->CONSTRAINT_NAME;
            DB::statement("ALTER TABLE conversations DROP FOREIGN KEY `$fkName2`");
        }

        // Drop the columns if they exist
        if (Schema::hasColumn('conversations', 'user_one_id') || Schema::hasColumn('conversations', 'user_two_id')) {
            Schema::table('conversations', function (Blueprint $table) {
                $drop = [];
                if (Schema::hasColumn('conversations', 'user_one_id')) $drop[] = 'user_one_id';
                if (Schema::hasColumn('conversations', 'user_two_id')) $drop[] = 'user_two_id';
                if (!empty($drop)) $table->dropColumn($drop);
            });
        }
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
