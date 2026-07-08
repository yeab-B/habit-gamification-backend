<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    /**
     * Run the migrations.
     */
            $table->id();


                $table->uuid('id')->primary();
                ->constrained('users')
                  ->cascadeOnDelete();


            // Challenge progress belongs to
            $table->foreignUuid('challenge_id')


            // Number of tasks completed that day
            $table->integer('completed_tasks')
                  ->default(0);


                $table->integer('tasks_completed')
            $table->integer('coins_earned')
                  ->default(0);

                // Total points earned that day
                $table->integer('total_points')
            $table->enum('status', [
                'completed',
                'missed'
                $table->boolean('is_completed')
                      ->default(false);

            // User can only have one check-in per challenge per day
            $table->unique([
                $table->softDeletes();
                'user_id',
                'challenge_id',
                $table->unique(['user_id', 'date']);

    /**
     * Reverse the migrations.

};