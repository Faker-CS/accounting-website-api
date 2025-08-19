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
        // First, update invalid reporter_id values to valid company IDs
        $firstCompany = \App\Models\Company::first();
        if ($firstCompany) {
            \DB::statement('UPDATE tasks SET reporter_id = ? WHERE reporter_id NOT IN (SELECT id FROM companies)', [$firstCompany->id]);
        }
        
        Schema::table('tasks', function (Blueprint $table) {
            // Add the new foreign key constraint to companies table
            $table->foreign('reporter_id')->references('id')->on('companies')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            // Drop the companies foreign key constraint
            $table->dropForeign(['reporter_id']);
            
            // Restore the original foreign key constraint to users table
            $table->foreign('reporter_id')->references('id')->on('users')->onDelete('cascade');
        });
    }
};
