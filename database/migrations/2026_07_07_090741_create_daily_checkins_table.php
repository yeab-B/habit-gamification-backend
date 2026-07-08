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
        Schema::create('daily_checkins', function (Blueprint $table) {

            $table->id();


            // User who checked in
            $table->foreignUuid('user_id')
                ->constrained('users')
                  ->cascadeOnDelete();


            // Challenge progress belongs to
            $table->foreignUuid('challenge_id')
                ->constrained('challenges')
                  ->cascadeOnDelete();


            // Check-in date
            $table->date('date');


            // Number of tasks completed that day
            $table->integer('completed_tasks')
                  ->default(0);


            // Total coins earned that day
            $table->integer('coins_earned')
                  ->default(0);


            // Daily status
            $table->enum('status', [
                'completed',
                'missed'
            ])
            ->default('completed');


            $table->timestamps();


            // User can only have one check-in per challenge per day
            $table->unique([
                'user_id',
                'challenge_id',
                'date'
            ]);

        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_checkins');
    }

};