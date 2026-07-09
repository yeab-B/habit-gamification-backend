<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('challenge_categories', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('challenge_id')
                ->constrained('challenges')
                ->cascadeOnDelete();

            $table->foreignUuid('category_id')
                ->constrained('categories')
                ->cascadeOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('challenge_categories');
    }
};