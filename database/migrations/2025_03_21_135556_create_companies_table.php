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
            $table->string('address');
            $table->date('founded');
            $table->string('logo', 100);
            $table->string('raison_sociale', 100);
            $table->decimal('capital_social', 15, 2);
            $table->string('numero_tva', 100);
            $table->string('numero_siren', 100);
            $table->string('numero_siret', 100);
            $table->enum('forme_juridique', ['EIRL', 'SARL', 'EURL', 'SAS', 'SASU', 'SA']);
            $table->enum('code_company_type', ['APE', 'NEF']);
            $table->string('code_company_value', 300);
            $table->unsignedBigInteger('status_id'); // Ensure this matches the type of the id column in statuses table
            $table->foreign('status_id')->references('id')->on('statuses')->onDelete('restrict')->onUpdate('cascade');
            $table->unsignedBigInteger('user_id'); // Ensure this matches the type of the id column in users table
            $table->foreign('user_id')->references('id')->on('users')->onDelete('restrict')->onUpdate('cascade');
            $table->timestamps();
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
