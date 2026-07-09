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
            $table->uuid('id')->primary();

            // User who owns streak
            $table->foreignUuid('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // Current consecutive days
            $table->integer('current_streak')
                ->default(0);

            // User's best streak record
            $table->integer('longest_streak')
                ->default(0);

            // Last day user completed required daily activity
            $table->date('last_completed_date')
                ->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique('user_id');
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
