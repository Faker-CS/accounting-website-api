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
        Schema::table('conversations', function (Blueprint $table) {
            if (!Schema::hasColumn('conversations', 'type')) {
                $table->string('type')->default('ONE_TO_ONE')->after('id');
            }
            if (!Schema::hasColumn('conversations', 'name')) {
                $table->string('name')->nullable()->after('type');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            if (Schema::hasColumn('conversations', 'type')) {
                $table->dropColumn('type');
            }
            if (Schema::hasColumn('conversations', 'name')) {
                $table->dropColumn('name');
            }
        });
    }
}; 