<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Category owner
            $table->foreignUuid('user_id')
                ->constrained('users')
                  ->cascadeOnDelete();

            // Category name
            // Example: Fitness, Study, Health
            $table->string('name');


            // Category icon
            // Example: 💪 📚 💰
            $table->string('icon')
                  ->nullable();

            // Color for UI representation
            $table->string('color')
                ->nullable();


            // Additional information
            $table->text('description')
                  ->nullable();


            $table->timestamps();
            $table->softDeletes();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};