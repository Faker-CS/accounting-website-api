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
        Schema::table('tasks', function (Blueprint $table) {
            // Drop the existing foreign key constraint
            $table->dropForeign(['reporter_id']);
            
            // Modify the column to be nullable
            $table->unsignedBigInteger('reporter_id')->nullable()->change();
            
            // Add the foreign key constraint back
            $table->foreign('reporter_id')->references('id')->on('companies')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            // Drop the foreign key constraint
            $table->dropForeign(['reporter_id']);
            
            // Make the column not nullable again
            $table->unsignedBigInteger('reporter_id')->nullable(false)->change();
            
            // Add the foreign key constraint back
            $table->foreign('reporter_id')->references('id')->on('companies')->onDelete('cascade');
        });
    }
};
