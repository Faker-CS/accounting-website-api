<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['user-user', 'user-company']);

            // User participants (nullable for user-company type)
            
            $table->unsignedBigInteger('user_two_id')->nullable();

            // Company participant (nullable for user-user type)
            $table->unsignedBigInteger('company_id')->nullable();

            $table->timestamps();

            // Foreign key constraints
            $table->foreign('user_two_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');

            // index for better performance on lookups
            $table->index(['type', 'user_one_id', 'user_two_id']);
            $table->index(['type', 'user_one_id', 'company_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('conversations');
    }
};