<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('budget_settings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->decimal('asrat_percentage', 5, 2)->default(10);
            $table->decimal('needs_percentage', 5, 2)->default(50);
            $table->decimal('emergency_percentage', 5, 2)->default(20);
            $table->decimal('investment_percentage', 5, 2)->default(10);
            $table->decimal('reward_percentage', 5, 2)->default(10);
            $table->timestamps();
        });

        Schema::create('budget_allocations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('income_id')->constrained('incomes')->cascadeOnDelete();
            $table->string('month', 7);
            $table->decimal('income_amount', 12, 2);
            $table->decimal('asrat_amount', 12, 2);
            $table->decimal('needs_amount', 12, 2);
            $table->decimal('emergency_amount', 12, 2);
            $table->decimal('investment_amount', 12, 2);
            $table->decimal('reward_amount', 12, 2);
            $table->timestamps();
            $table->index(['user_id', 'month']);
        });

        Schema::create('expense_categories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['user_id', 'name']);
        });

        Schema::create('expenses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('category_id')->constrained('expense_categories')->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->date('expense_date');
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['user_id', 'expense_date']);
            $table->index(['user_id', 'category_id']);
        });

        Schema::create('emergency_funds', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('goal_amount', 12, 2);
            $table->decimal('current_amount', 12, 2)->default(0);
            $table->enum('status', ['active', 'completed'])->default('active');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('emergency_transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('emergency_fund_id')->constrained('emergency_funds')->cascadeOnDelete();
            $table->enum('type', ['deposit', 'withdraw']);
            $table->decimal('amount', 12, 2);
            $table->date('transaction_date');
            $table->timestamps();
        });

        Schema::create('investments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->string('type');
            $table->text('description')->nullable();
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('investment_transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('investment_id')->constrained('investments')->cascadeOnDelete();
            $table->enum('type', ['deposit', 'withdraw', 'profit', 'loss']);
            $table->decimal('amount', 12, 2);
            $table->date('date');
            $table->timestamps();
        });

        Schema::create('reward_wallets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->decimal('available_balance', 12, 2)->default(0);
            $table->decimal('locked_balance', 12, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('reward_transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('reward_wallet_id')->constrained('reward_wallets')->cascadeOnDelete();
            $table->enum('type', ['earn', 'spend']);
            $table->decimal('amount', 12, 2);
            $table->string('source');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reward_transactions');
        Schema::dropIfExists('reward_wallets');
        Schema::dropIfExists('investment_transactions');
        Schema::dropIfExists('investments');
        Schema::dropIfExists('emergency_transactions');
        Schema::dropIfExists('emergency_funds');
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('expense_categories');
        Schema::dropIfExists('budget_allocations');
        Schema::dropIfExists('budget_settings');
    }
};
