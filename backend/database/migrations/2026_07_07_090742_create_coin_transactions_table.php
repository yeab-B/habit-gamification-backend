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
        Schema::create('coin_transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // User whose balance changed
            $table->foreignUuid('user_id')
                ->constrained('users')
                ->cascadeOnDelete();


            // Transaction type
            $table->enum('type', [
                'earn',
                'spend',
                'bonus',
                'penalty',
            ]);

            $table->string('source');

            // Positive value only. Type determines whether it adds or deducts.
            $table->integer('amount');
            $table->integer('balance_after');


            // Explanation
            $table->text('description')
                ->nullable();

            $table->uuid('reference_id')
                ->nullable();

            $table->timestamps();
            $table->softDeletes();

        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coin_transactions');
    }

};
