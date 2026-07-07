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
        Schema::create('categories', function (Blueprint $table) {

            $table->id();

            // Category owner
            $table->foreignId('user_id')
                  ->constrained()
                  ->cascadeOnDelete();

            // Category name
            // Example: Fitness, Study, Health
            $table->string('name');


            // Category icon
            // Example: 💪 📚 💰
            $table->string('icon')
                  ->nullable();


            // Additional information
            $table->text('description')
                  ->nullable();


            $table->timestamps();

        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};