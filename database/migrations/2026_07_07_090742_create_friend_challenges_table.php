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
        Schema::create('friend_challenges', function (Blueprint $table) {

            $table->id();


            // Main challenge
            $table->foreignId('challenge_id')
                  ->constrained()
                  ->cascadeOnDelete();


            // User who creates challenge
            $table->foreignUuid('challenger_id')
                  ->constrained('users')
                  ->cascadeOnDelete();


            // User who receives challenge
            $table->foreignUuid('opponent_id')
                  ->constrained('users')
                  ->cascadeOnDelete();


            // Challenge status
            $table->enum('status', [

                'pending',
                'accepted',
                'rejected',
                'completed',
                'cancelled'

            ])
            ->default('pending');


            // Winner user
            $table->foreignUuid('winner_id')
                  ->nullable()
                  ->constrained('users')
                  ->nullOnDelete();


            // Coins at stake
            $table->integer('stake_coins')
                  ->default(0);


            // Completion date
            $table->timestamp('completed_at')
                  ->nullable();


            $table->timestamps();


        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('friend_challenges');
    }

};