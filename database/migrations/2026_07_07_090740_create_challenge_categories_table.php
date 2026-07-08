<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('challenge_categories', function (Blueprint $table) {
            $table->id();

            $table->foreignId('challenge_id')
                ->constrained()
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