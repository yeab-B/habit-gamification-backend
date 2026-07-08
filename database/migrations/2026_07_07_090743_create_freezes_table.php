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
        Schema::create('freezes', function (Blueprint $table) {

            $table->id();


            // User sending freeze
            $table->foreignUuid('sender_id')
                  ->constrained('users')
                  ->cascadeOnDelete();


            // User receiving freeze
            $table->foreignUuid('receiver_id')
                  ->constrained('users')
                  ->cascadeOnDelete();


            // Challenge being protected
            $table->foreignUuid('challenge_id')
                ->constrained('challenges')
                  ->cascadeOnDelete();


            // Freeze cost
            $table->integer('coins_spent')
                  ->default(3);


            // Freeze status
            $table->enum('status', [

                'sent',
                'used',
                'expired'

            ])
            ->default('sent');


            // When freeze was used
            $table->timestamp('used_at')
                  ->nullable();


            $table->timestamps();

        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('freezes');
    }

};