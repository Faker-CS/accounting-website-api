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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('company_name', 100);
            $table->string('description', 100);
            $table->string('logo', 100);
            $table->date('founded');
            $table->string('raison_sociale', 100);
            $table->decimal('capital_social', 15, 2);
            $table->string('numero_tva', 100);
            $table->string('numero_siren', 100)->unique();
            $table->string('numero_siret', 100)->unique();
            $table->enum('forme_juridique', ['EIRL', 'SARL', 'EURL', 'SAS', 'SASU', 'SA']);
            $table->enum('code_company_type', ['APE', 'NEF']);
            $table->string('code_company_value', 300);
            $table->string('adresse_siege_social', 255);
            $table->string('code_postale', 10);
            $table->string('ville', 100);
            $table->string('convention_collective', 100);
            $table->decimal('chiffre_affaire', 15, 2);
            $table->decimal('tranche_a', 15, 2);
            $table->decimal('tranche_b', 15, 2);
            $table->integer('nombre_salaries');
            $table->decimal('moyenne_age', 5, 2);
            $table->integer('nombre_salaries_cadres');
            $table->decimal('moyenne_age_cadres', 5, 2);
            $table->integer('nombre_salaries_non_cadres');
            $table->decimal('moyenne_age_non_cadres', 5, 2);
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();
    
            // Indexes
            $table->index('company_name');
            $table->index('forme_juridique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
