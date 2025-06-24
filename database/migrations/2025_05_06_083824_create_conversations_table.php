<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->string('type')->default('ONE_TO_ONE'); // ONE_TO_ONE or GROUP
            $table->string('name')->nullable(); // For group conversations
            $table->timestamps();

            // Indexes for better performance
            $table->index('type');
        });
    }

    public function down()
    {
        Schema::dropIfExists('conversations');
    }
};