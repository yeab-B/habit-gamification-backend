<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->uuid('id')->primary();


            // Task owner
            $table->foreignUuid('user_id')
                ->constrained('users')
                  ->cascadeOnDelete();


            // Category where this task belongs
            $table->foreignUuid('category_id')
                ->constrained('categories')
                  ->cascadeOnDelete();


            // Task title
            // Example: Running, Reading Book
            $table->string('title');


            // Task description
            $table->text('description')
                  ->nullable();


            // Reward coins after completing task
            // Example: Running = 20 coins
            $table->integer('points')
                  ->default(0);

            $table->boolean('is_active')
                  ->default(true);


            $table->timestamps();
            $table->softDeletes();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};