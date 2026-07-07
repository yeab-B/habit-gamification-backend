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
        Schema::create('user_achievements', function (Blueprint $table) {

            $table->id();


            // User who earned achievement
            $table->foreignId('user_id')
                  ->constrained()
                  ->cascadeOnDelete();


            // Achievement unlocked
            $table->foreignId('achievement_id')
                  ->constrained()
                  ->cascadeOnDelete();


            // Date achievement was earned
            $table->timestamp('earned_at');


            $table->timestamps();


            // Prevent duplicate achievement
            $table->unique([
                'user_id',
                'achievement_id'
            ]);

        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_achievements');
    }

};