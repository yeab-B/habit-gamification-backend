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
        Schema::create('challenge_users', function (Blueprint $table) {

            $table->id();


            // Challenge that user joined
            $table->foreignId('challenge_id')
                  ->constrained()
                  ->cascadeOnDelete();


            // User who joined challenge
            $table->foreignUuid('user_id')
                ->constrained('users')
                  ->cascadeOnDelete();


            // Participant status
            $table->enum('status', [
                'pending',
                'accepted',
                'rejected',
                'left'
            ])
            ->default('pending');


            // Date user joined
            $table->timestamp('joined_at')
                  ->nullable();


            $table->timestamps();


            // Prevent same user joining same challenge twice
            $table->unique([
                'challenge_id',
                'user_id'
            ]);

        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('challenge_users');
    }

};