<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_completions', function (Blueprint $table) {
            $table->uuid('id')->primary();


            // User who completed task
            $table->foreignUuid('user_id')
                ->constrained('users')
                  ->cascadeOnDelete();

            $table->timestamp('completed_at');
            $table->date('completion_date');
            $table->date('completed_date');


            $table->softDeletes();
            // Points earned from this completion
            $table->integer('points_earned')
                  ->default(0);


            $table->timestamps();
                'completion_date'

            // Prevent completing same task multiple times per day
            $table->unique([
                'user_id',
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_completions');
    }

};