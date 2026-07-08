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
        Schema::create('challenges', function (Blueprint $table) {

            $table->id();


            // User who created the challenge
            $table->foreignUuid('user_id')
                ->constrained('users')
                  ->cascadeOnDelete();


            // Challenge name
            // Example: 30 Days Better Me
            $table->string('title');


            // Challenge explanation
            $table->text('description')
                  ->nullable();


            // Number of challenge days
            // Example: 7, 21, 30
            $table->integer('duration');


            // Challenge starting date
            $table->date('start_date');


            // Challenge ending date
            $table->date('end_date');


            // Who can see/join the challenge
            $table->enum('visibility', [
                'private',
                'friends',
                'public'
            ])
            ->default('private');


            // Challenge current status
            $table->enum('status', [
                'active',
                'completed',
                'cancelled'
            ])
            ->default('active');


            $table->timestamps();

        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('challenges');
    }
};