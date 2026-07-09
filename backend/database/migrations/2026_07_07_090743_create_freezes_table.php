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
            $table->uuid('id')->primary();


            // User sending freeze
            $table->foreignUuid('sender_id')
                  ->constrained('users')
                  ->cascadeOnDelete();


            // User receiving freeze
            $table->foreignUuid('receiver_id')
                  ->constrained('users')
                  ->cascadeOnDelete();


            // Freeze cost
            $table->integer('cost')
                  ->default(3);


            // Freeze status
            $table->enum('status', [
                'available',
                'used',
                'expired'

            ])
            ->default('available');


            // When freeze was used
            $table->timestamp('used_at')
                  ->nullable();

            $table->text('reason')
                  ->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['receiver_id', 'status']);
            $table->index('sender_id');

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
