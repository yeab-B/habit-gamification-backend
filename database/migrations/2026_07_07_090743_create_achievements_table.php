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
        Schema::create('achievements', function (Blueprint $table) {

            $table->id();


            // Achievement name
            // Example:
            // First Challenge Completed
            $table->string('name');


            // Explanation
            $table->text('description');


            // Achievement icon
            // Example: 🔥 🏆 ⭐
            $table->string('icon')
                  ->nullable();


            // Achievement category
            $table->enum('type', [

                'streak',
                'challenge',
                'task',
                'coin',
                'social'

            ]);


            // Requirement value
            // Example:
            // 30 for 30 day streak
            $table->integer('requirement')
                  ->nullable();


            // Reward coins
            $table->integer('reward_coins')
                  ->default(0);


            $table->timestamps();

        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('achievements');
    }

};