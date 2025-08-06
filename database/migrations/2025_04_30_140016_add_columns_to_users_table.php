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
        Schema::table('users', function (Blueprint $table) {
            $table->integer('phoneNumber')->nullable()->after('email');
            $table->string('city', 100)->nullable()->after('phoneNumber');
            $table->string('state', 50)->nullable()->after('city');
            $table->string('address', 255)->nullable()->after('state');
            $table->integer('zipCode')->nullable()->after('address');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['state', 'phoneNumber', 'city', 'address', 'zipCode']);
        });
    }
};