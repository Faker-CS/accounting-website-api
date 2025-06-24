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
        // Drop pivot tables first
        Schema::dropIfExists('company_industry');
        Schema::dropIfExists('company_activity');
        
        // Drop main tables
        Schema::dropIfExists('industries');
        Schema::dropIfExists('activities');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate industries table
        Schema::create('industries', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->timestamps();
        });

        // Recreate activities table
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10);
            $table->string('name', 100);
            $table->timestamps();
        });

        // Recreate pivot tables
        Schema::create('company_industry', function (Blueprint $table) {
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('industry_id')->constrained()->onDelete('cascade');
            $table->primary(['company_id', 'industry_id']);
        });

        Schema::create('company_activity', function (Blueprint $table) {
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('activity_id')->constrained()->onDelete('cascade');
            $table->primary(['company_id', 'activity_id']);
        });
    }
}; 