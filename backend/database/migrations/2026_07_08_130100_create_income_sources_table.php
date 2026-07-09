<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('income_sources', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('user_id')
                ->nullable()
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_default')->default(false);

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['user_id', 'name']);
            $table->index(['user_id', 'is_default']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('income_sources');
    }
};
