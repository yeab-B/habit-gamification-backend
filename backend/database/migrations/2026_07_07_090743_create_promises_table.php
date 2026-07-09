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
            $table->uuid('id')->primary();

            // User who made promise
            $table->foreignUuid('user_id')
                ->constrained('users')
                ->cascadeOnDelete();


            // Day the user missed required progress
            $table->date('promise_date');

            // Day that must be completed to keep the promise
            $table->date('validation_date');


            // Promise result
            $table->enum('status', [
                'pending',
                'fulfilled',
                'broken',
                'expired',
            ])->default('pending');

            $table->text('reason')
                ->nullable();

            $table->integer('reward_coins')
                ->default(0);

            $table->integer('penalty_coins')
                ->default(0);

            $table->timestamp('validated_at')
                ->nullable();


            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'promise_date']);
            $table->index(['user_id', 'status']);

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
