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
        Schema::create('promises', function (Blueprint $table) {

            $table->id();


            // User who made promise
            $table->foreignUuid('user_id')
                ->constrained('users')
                  ->cascadeOnDelete();


            // Related challenge
            $table->foreignUuid('challenge_id')
                ->constrained('challenges')
                  ->cascadeOnDelete();


            // Friend challenge if applicable
            $table->foreignId('friend_challenge_id')
                ->nullable()
                ->constrained('friend_challenges')
                  ->cascadeOnDelete();


            // Promise message
            $table->text('message')
                  ->nullable();


            // Promise deadline
            $table->date('promise_date');


            // Promise result
            $table->enum('status', [

                'active',
                'completed',
                'broken',
                'expired'

            ])
            ->default('active');


            // When promise completed
            $table->timestamp('completed_at')
                  ->nullable();


            $table->timestamps();

        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promises');
    }

};