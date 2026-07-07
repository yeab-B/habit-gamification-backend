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
        Schema::create('tasks', function (Blueprint $table) {

            $table->id();


            // Task owner
            $table->foreignId('user_id')
                  ->constrained()
                  ->cascadeOnDelete();


            // Category where this task belongs
            $table->foreignId('category_id')
                  ->constrained()
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


            // Task difficulty level
            $table->enum('difficulty', [
                'easy',
                'medium',
                'hard'
            ])
            ->default('easy');


            // How often task should be completed
            $table->enum('frequency', [
                'daily',
                'weekly'
            ])
            ->default('daily');


            $table->timestamps();

        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};