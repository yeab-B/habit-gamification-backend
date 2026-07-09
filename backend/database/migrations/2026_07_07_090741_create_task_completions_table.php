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

            $table->foreignUuid('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignUuid('task_id')
                ->constrained('tasks')
                ->cascadeOnDelete();

            $table->timestamp('completed_at');
            $table->date('completion_date');

            $table->timestamps();
            $table->softDeletes();

            $table->unique([
                'user_id',
                'task_id',
                'completion_date',
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_completions');
    }
};