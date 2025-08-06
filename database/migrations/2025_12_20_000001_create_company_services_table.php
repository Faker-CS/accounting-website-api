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
        Schema::create('company_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('service_id')->constrained()->onDelete('cascade');
            $table->enum('frequency', ['mensuelle', 'trimestrielle', 'annuelle'])->default('mensuelle');
            $table->enum('status', ['actif', 'inactif', 'annulé'])->default('actif');
            $table->date('declaration_date')->nullable();
            $table->enum('added_by', ['comptable', 'entreprise'])->default('comptable');
            $table->text('notes')->nullable();
            $table->timestamps();

            // Unique constraint to prevent duplicate services per company
            $table->unique(['company_id', 'service_id']);
            
            // Indexes for better performance
            $table->index(['company_id', 'status']);
            $table->index(['service_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_services');
    }
}; 