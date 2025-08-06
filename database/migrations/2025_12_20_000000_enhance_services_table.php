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
        Schema::table('services', function (Blueprint $table) {
            $table->enum('period_type', ['mensuelle', 'trimestrielle', 'annuelle'])->default('mensuelle')->after('description');
            $table->boolean('is_default')->default(false)->after('period_type');
            $table->decimal('price', 10, 2)->nullable()->after('is_default');
            $table->text('requirements')->nullable()->after('price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn(['period_type', 'is_default', 'price', 'requirements']);
        });
    }
}; 