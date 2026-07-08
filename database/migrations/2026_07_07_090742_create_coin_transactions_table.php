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
        Schema::create('coin_transactions', function (Blueprint $table) {

            $table->id();


            // User whose balance changed
            $table->foreignUuid('user_id')
                ->constrained('users')
                  ->cascadeOnDelete();


            // Related challenge
            $table->foreignId('challenge_id')
                  ->nullable()
                  ->constrained()
                  ->cascadeOnDelete();


            // Related task
            $table->foreignUuid('task_id')
                  ->nullable()
                ->constrained('tasks')
                  ->cascadeOnDelete();


            // Transaction type
            $table->enum('type', [

                // Earned coins
                'earned',

                // Lost coins
                'lost',

                // Transfer to another user
                'transfer',

                // Freeze cost
                'freeze'

            ]);


            // Positive or negative amount
            $table->integer('amount');


            // Explanation
            $table->string('description')
                  ->nullable();


            $table->timestamps();

        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coin_transactions');
    }

};