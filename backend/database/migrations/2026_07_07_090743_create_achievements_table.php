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
            $table->uuid('id')->primary();


            // Achievement name
            // Example:
            // First Challenge Completed
            $table->string('name');

            $table->string('slug')
                  ->unique();


            // Explanation
            $table->text('description');


            // Achievement icon
            // Example: 🔥 🏆 ⭐
            $table->string('icon')
                  ->nullable();


            $table->string('category');

            $table->string('condition_type');

            $table->integer('condition_value');


            // Reward coins
            $table->integer('reward_coins')
                  ->default(0);

            $table->boolean('is_active')
                  ->default(true);

            $table->timestamps();
            $table->softDeletes();

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
