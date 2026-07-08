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
        Schema::create('streaks', function (Blueprint $table) {

            $table->id();


            // User who owns streak
            $table->foreignUuid('user_id')
                ->constrained('users')
                  ->cascadeOnDelete();


            // Challenge this streak belongs to
            $table->foreignId('challenge_id')
                  ->constrained()
                  ->cascadeOnDelete();


            // Current consecutive days
            $table->integer('current_streak')
                  ->default(0);


            // User's best streak record
            $table->integer('longest_streak')
                  ->default(0);


            // Last day user completed challenge
            $table->date('last_completed_date')
                  ->nullable();


            $table->timestamps();


            // One streak per user per challenge
            $table->unique([
                'user_id',
                'challenge_id'
            ]);

        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('streaks');
    }

};