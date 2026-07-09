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
        Schema::create('friendships', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // User who sends request
            $table->foreignUuid('sender_id')
                ->constrained('users')
                ->cascadeOnDelete();


            // User who receives request
            $table->foreignUuid('receiver_id')
                ->constrained('users')
                ->cascadeOnDelete();


            // Friendship status
            $table->enum('status', [
                'pending',
                'accepted',
                'rejected',
                'blocked',
            ])->default('pending');


            // When request was accepted or rejected
            $table->timestamp('responded_at')
                ->nullable();


            $table->timestamps();
            $table->softDeletes();

            $table->index(['sender_id', 'receiver_id']);


            // Prevent duplicate requests
            $table->unique([
                'sender_id',
                'receiver_id',
            ]);

        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('friendships');
    }

};
