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
        Schema::create('task_completions', function (Blueprint $table) {

            $table->id();


            // User who completed task
            $table->foreignUuid('user_id')
                ->constrained('users')
                  ->cascadeOnDelete();


            // Completed task
            $table->foreignUuid('task_id')
                ->constrained('tasks')
                  ->cascadeOnDelete();


            // Challenge where task was completed
            $table->foreignId('challenge_id')
                  ->constrained()
                  ->cascadeOnDelete();


            // Date task was completed
            $table->date('completed_date');


            // Points earned from this completion
            $table->integer('points_earned')
                  ->default(0);


            $table->timestamps();


            // Prevent completing same task multiple times per day
            $table->unique([
                'user_id',
                'task_id',
                'completed_date'
            ]);

        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_completions');
    }

};