<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incomes', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignUuid('income_source_id')
                ->constrained('income_sources')
                ->cascadeOnDelete();

            $table->decimal('amount', 12, 2);
            $table->string('currency')->default('ETB');
            $table->date('income_date');
            $table->text('description')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'income_date']);
            $table->index(['user_id', 'income_source_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incomes');
    }
};
