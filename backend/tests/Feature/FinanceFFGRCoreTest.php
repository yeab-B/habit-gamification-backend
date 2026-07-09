<?php

namespace Tests\Feature;

use App\Models\Finance\BudgetAllocation;
use App\Models\Finance\BudgetSetting;
use App\Models\Finance\EmergencyFund;
use App\Models\Finance\Expense;
use App\Models\Finance\ExpenseCategory;
use App\Models\Finance\Investment;
use App\Models\Finance\InvestmentTransaction;
use App\Models\Finance\RewardWallet;
use App\Services\Finance\BudgetService;
use App\Models\Income;
use App\Models\IncomeSource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class FinanceFFGRCoreTest extends TestCase
{
    use RefreshDatabase;

    // =====================
    // BUDGET TESTS
    // =====================

    public function test_budget_settings_have_default_values(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/finance/budget')
            ->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.settings.asrat_percentage', '10.00')
            ->assertJsonPath('data.settings.needs_percentage', '50.00')
            ->assertJsonPath('data.settings.emergency_percentage', '20.00')
            ->assertJsonPath('data.settings.investment_percentage', '20.00')
            ->assertJsonPath('data.settings.reward_percentage', '10.00');
    }

    public function test_user_can_update_budget_settings(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->postJson('/api/finance/budget/settings', [
            'asrat_percentage' => 15,
            'needs_percentage' => 40,
            'emergency_percentage' => 25,
            'investment_percentage' => 25,
            'reward_percentage' => 10,
        ])->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('message', 'Budget settings updated successfully')
            ->assertJsonPath('data.asrat_percentage', '15.00')
            ->assertJsonPath('data.needs_percentage', '40.00')
            ->assertJsonPath('data.emergency_percentage', '25.00')
            ->assertJsonPath('data.investment_percentage', '25.00')
            ->assertJsonPath('data.reward_percentage', '10.00');
    }

    public function test_asrat_calculated_correctly(): void
    {
        $service = app(BudgetService::class);
        $result = $service->calculateAsrat(50000);
        $this->assertSame('5000.00', $result['asrat']);
        $this->assertSame('45000.00', $result['remaining']);
    }

    public function test_allocation_created_after_income(): void
    {
        $user = User::factory()->create();
        $source = IncomeSource::query()->create(['name' => 'Salary', 'is_default' => true]);

        Sanctum::actingAs($user);

        $this->postJson('/api/incomes', [
            'income_source_id' => $source->id,
            'amount' => 50000,
            'currency' => 'ETB',
            'income_date' => '2026-07-08',
        ])->assertCreated();

        $this->assertDatabaseHas('budget_allocations', [
            'user_id' => $user->id,
            'income_amount' => 50000,
            'asrat_amount' => 5000,
            'needs_amount' => 22500,
            'emergency_amount' => 9000,
            'investment_amount' => 9000,
            'reward_amount' => 4500,
        ]);
    }

    public function test_budget_shows_monthly_totals(): void
    {
        $user = User::factory()->create();
        $month = now()->format('Y-m');

        BudgetAllocation::query()->create([
            'user_id' => $user->id,
            'income_id' => $this->makeIncome($user)->id,
            'month' => $month,
            'income_amount' => 50000,
            'asrat_amount' => 5000,
            'needs_amount' => 22500,
            'emergency_amount' => 9000,
            'investment_amount' => 9000,
            'reward_amount' => 4500,
        ]);

        Sanctum::actingAs($user);

        $this->getJson('/api/finance/budget')
            ->assertOk()
            ->assertJsonPath('data.month', $month)
            ->assertJsonPath('data.totals.income', '50000.00')
            ->assertJsonPath('data.totals.asrat', '5000.00')
            ->assertJsonPath('data.totals.needs', '22500.00')
            ->assertJsonPath('data.totals.emergency', '9000.00')
            ->assertJsonPath('data.totals.investment', '9000.00')
            ->assertJsonPath('data.totals.reward', '4500.00');
    }

    public function test_custom_percentages_affect_allocation(): void
    {
        $user = User::factory()->create();

        BudgetSetting::query()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'asrat_percentage' => 20,
                'needs_percentage' => 30,
                'emergency_percentage' => 20,
                'investment_percentage' => 20,
                'reward_percentage' => 30,
            ]
        );

        $service = app(BudgetService::class);
        $allocation = $service->calculateAllocation(50000, $service->getSettings($user));

        $this->assertSame('10000.00', $allocation['asrat_amount']);
        $this->assertSame('12000.00', $allocation['needs_amount']);
        $this->assertSame('8000.00', $allocation['emergency_amount']);
        $this->assertSame('8000.00', $allocation['investment_amount']);
        $this->assertSame('12000.00', $allocation['reward_amount']);
    }

    public function test_user_isolation_on_budget(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        BudgetAllocation::query()->create([
            'user_id' => $user1->id,
            'income_id' => $this->makeIncome($user1)->id,
            'month' => now()->format('Y-m'),
            'income_amount' => 50000,
            'asrat_amount' => 5000,
            'needs_amount' => 22500,
            'emergency_amount' => 9000,
            'investment_amount' => 9000,
            'reward_amount' => 4500,
        ]);

        Sanctum::actingAs($user2);

        $this->getJson('/api/finance/budget')
            ->assertOk()
            ->assertJsonPath('data.totals.income', '0.00')
            ->assertJsonPath('data.totals.needs', '0.00');
    }

    // =====================
    // EXPENSE TESTS
    // =====================

    public function test_user_can_create_expense(): void
    {
        $user = User::factory()->create();
        $category = $this->expenseCategory();

        Sanctum::actingAs($user);

        $this->postJson('/api/finance/expenses', [
            'category_id' => $category->id,
            'amount' => 3000,
            'expense_date' => '2026-07-08',
            'description' => 'Lunch',
        ])->assertCreated()
            ->assertJsonPath('status', true)
            ->assertJsonPath('message', 'Expense created successfully')
            ->assertJsonPath('data.amount', '3000.00')
            ->assertJsonPath('data.category', 'Food');

        $this->assertDatabaseHas('expenses', [
            'user_id' => $user->id,
            'category_id' => $category->id,
            'amount' => 3000,
        ]);
    }

    public function test_user_can_update_expense(): void
    {
        $user = User::factory()->create();
        $category = $this->expenseCategory();
        $expense = Expense::query()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'amount' => 3000,
            'expense_date' => '2026-07-08',
        ]);

        Sanctum::actingAs($user);

        $this->putJson('/api/finance/expenses/' . $expense->id, [
            'category_id' => $category->id,
            'amount' => 3500,
            'expense_date' => '2026-07-08',
            'description' => 'Updated lunch',
        ])->assertOk()
            ->assertJsonPath('message', 'Expense updated successfully')
            ->assertJsonPath('data.amount', '3500.00');
    }

    public function test_user_can_delete_expense(): void
    {
        $user = User::factory()->create();
        $category = $this->expenseCategory();
        $expense = Expense::query()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'amount' => 3000,
            'expense_date' => '2026-07-08',
        ]);

        Sanctum::actingAs($user);

        $this->deleteJson('/api/finance/expenses/' . $expense->id)
            ->assertOk()
            ->assertJsonPath('message', 'Expense deleted successfully');

        $this->assertSoftDeleted('expenses', ['id' => $expense->id]);
    }

    public function test_user_cannot_modify_other_users_expense(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $category = $this->expenseCategory();
        $expense = Expense::query()->create([
            'user_id' => $user1->id,
            'category_id' => $category->id,
            'amount' => 3000,
            'expense_date' => '2026-07-08',
        ]);

        Sanctum::actingAs($user2);

        $this->putJson('/api/finance/expenses/' . $expense->id, [
            'category_id' => $category->id,
            'amount' => 5000,
            'expense_date' => '2026-07-08',
        ])->assertForbidden();

        $this->deleteJson('/api/finance/expenses/' . $expense->id)
            ->assertForbidden();
    }

    public function test_user_only_sees_own_expenses(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $category = $this->expenseCategory();

        Expense::query()->create([
            'user_id' => $user1->id,
            'category_id' => $category->id,
            'amount' => 1000,
            'expense_date' => '2026-07-08',
        ]);

        Sanctum::actingAs($user2);

        $this->getJson('/api/finance/expenses')
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_expense_category_validation(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $this->postJson('/api/finance/expenses', [
            'category_id' => 'non-existent-id',
            'amount' => 3000,
            'expense_date' => '2026-07-08',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['category_id']);
    }

    public function test_expense_amount_must_be_positive(): void
    {
        $user = User::factory()->create();
        $category = $this->expenseCategory();

        Sanctum::actingAs($user);

        $this->postJson('/api/finance/expenses', [
            'category_id' => $category->id,
            'amount' => -100,
            'expense_date' => '2026-07-08',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['amount']);
    }

    // =====================
    // EMERGENCY FUND TESTS
    // =====================

    public function test_user_can_create_emergency_fund(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $this->postJson('/api/finance/emergency-funds', [
            'goal_amount' => 100000,
        ])->assertCreated()
            ->assertJsonPath('status', true)
            ->assertJsonPath('message', 'Emergency fund created successfully')
            ->assertJsonPath('data.goal_amount', '100000.00')
            ->assertJsonPath('data.current_amount', '0.00')
            ->assertJsonPath('data.status', 'active')
            ->assertJsonPath('data.progress_percentage', 0);
    }

    public function test_user_can_deposit_to_emergency_fund(): void
    {
        $user = User::factory()->create();
        $fund = EmergencyFund::query()->create([
            'user_id' => $user->id,
            'goal_amount' => 100000,
            'current_amount' => 0,
            'status' => 'active',
        ]);

        Sanctum::actingAs($user);

        $this->postJson("/api/finance/emergency-funds/{$fund->id}/deposit", [
            'amount' => 40000,
        ])->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('message', 'Emergency fund deposit recorded successfully')
            ->assertJsonPath('data.current_amount', '40000.00')
            ->assertJsonPath('data.progress_percentage', 40);

        $this->assertDatabaseHas('emergency_transactions', [
            'emergency_fund_id' => $fund->id,
            'type' => 'deposit',
            'amount' => 40000,
        ]);
    }

    public function test_user_can_withdraw_from_emergency_fund(): void
    {
        $user = User::factory()->create();
        $fund = EmergencyFund::query()->create([
            'user_id' => $user->id,
            'goal_amount' => 100000,
            'current_amount' => 50000,
            'status' => 'active',
        ]);

        Sanctum::actingAs($user);

        $this->postJson("/api/finance/emergency-funds/{$fund->id}/withdraw", [
            'amount' => 10000,
        ])->assertOk()
            ->assertJsonPath('data.current_amount', '40000.00');

        $this->assertDatabaseHas('emergency_transactions', [
            'emergency_fund_id' => $fund->id,
            'type' => 'withdraw',
            'amount' => 10000,
        ]);
    }

    public function test_emergency_fund_completes_when_goal_reached(): void
    {
        $user = User::factory()->create();
        $fund = EmergencyFund::query()->create([
            'user_id' => $user->id,
            'goal_amount' => 100000,
            'current_amount' => 90000,
            'status' => 'active',
        ]);

        Sanctum::actingAs($user);

        $this->postJson("/api/finance/emergency-funds/{$fund->id}/deposit", [
            'amount' => 10000,
        ])->assertOk()
            ->assertJsonPath('data.status', 'completed')
            ->assertJsonPath('data.progress_percentage', 100);

        $this->assertDatabaseHas('emergency_funds', [
            'id' => $fund->id,
            'status' => 'completed',
        ]);
    }

    public function test_cannot_withdraw_below_zero(): void
    {
        $user = User::factory()->create();
        $fund = EmergencyFund::query()->create([
            'user_id' => $user->id,
            'goal_amount' => 100000,
            'current_amount' => 5000,
            'status' => 'active',
        ]);

        Sanctum::actingAs($user);

        $this->postJson("/api/finance/emergency-funds/{$fund->id}/withdraw", [
            'amount' => 10000,
        ])->assertStatus(422);
    }

    public function test_emergency_progress_calculated_correctly(): void
    {
        $user = User::factory()->create();
        $fund = EmergencyFund::query()->create([
            'user_id' => $user->id,
            'goal_amount' => 100000,
            'current_amount' => 40000,
            'status' => 'active',
        ]);

        Sanctum::actingAs($user);

        $this->getJson('/api/finance/emergency-funds')
            ->assertOk()
            ->assertJsonPath('data.0.progress_percentage', 40);
    }

    public function test_user_isolation_on_emergency_funds(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        EmergencyFund::query()->create([
            'user_id' => $user1->id,
            'goal_amount' => 100000,
            'current_amount' => 50000,
            'status' => 'active',
        ]);

        Sanctum::actingAs($user2);

        $this->getJson('/api/finance/emergency-funds')
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    // =====================
    // INVESTMENT TESTS
    // =====================

    public function test_user_can_create_investment(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $this->postJson('/api/finance/investments', [
            'name' => 'Tech Startup',
            'type' => 'Business',
            'description' => 'Seed round investment',
            'total_amount' => 50000,
        ])->assertCreated()
            ->assertJsonPath('status', true)
            ->assertJsonPath('message', 'Investment created successfully')
            ->assertJsonPath('data.name', 'Tech Startup')
            ->assertJsonPath('data.type', 'Business')
            ->assertJsonPath('data.total_amount', '50000.00');
    }

    public function test_user_can_add_deposit_transaction(): void
    {
        $user = User::factory()->create();
        $investment = Investment::query()->create([
            'user_id' => $user->id,
            'name' => 'Test Investment',
            'type' => 'Savings',
            'total_amount' => 0,
        ]);

        Sanctum::actingAs($user);

        $this->postJson("/api/finance/investments/{$investment->id}/transactions", [
            'type' => 'deposit',
            'amount' => 10000,
        ])->assertOk()
            ->assertJsonPath('message', 'Investment transaction recorded successfully')
            ->assertJsonPath('data.total_amount', '10000.00');
    }

    public function test_user_can_add_profit_transaction(): void
    {
        $user = User::factory()->create();
        $investment = Investment::query()->create([
            'user_id' => $user->id,
            'name' => 'Stocks',
            'type' => 'Stocks',
            'total_amount' => 100000,
        ]);

        Sanctum::actingAs($user);

        $this->postJson("/api/finance/investments/{$investment->id}/transactions", [
            'type' => 'profit',
            'amount' => 5000,
        ])->assertOk()
            ->assertJsonPath('data.total_amount', '105000.00');
    }

    public function test_investment_cannot_go_negative(): void
    {
        $user = User::factory()->create();
        $investment = Investment::query()->create([
            'user_id' => $user->id,
            'name' => 'Test',
            'type' => 'Savings',
            'total_amount' => 1000,
        ]);

        Sanctum::actingAs($user);

        $this->postJson("/api/finance/investments/{$investment->id}/transactions", [
            'type' => 'withdraw',
            'amount' => 2000,
        ])->assertStatus(422);
    }

    public function test_user_portfolio_shows_total_value(): void
    {
        $user = User::factory()->create();

        Investment::query()->create([
            'user_id' => $user->id,
            'name' => 'Business A',
            'type' => 'Business',
            'total_amount' => 50000,
        ]);

        Investment::query()->create([
            'user_id' => $user->id,
            'name' => 'Crypto B',
            'type' => 'Crypto',
            'total_amount' => 25000,
        ]);

        Sanctum::actingAs($user);

        $this->getJson('/api/finance/investments')
            ->assertOk()
            ->assertJsonPath('portfolio.total_value', '75000.00')
            ->assertJsonCount(2, 'data');
    }

    public function test_user_isolation_on_investments(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        Investment::query()->create([
            'user_id' => $user1->id,
            'name' => 'Private',
            'type' => 'Savings',
            'total_amount' => 50000,
        ]);

        Sanctum::actingAs($user2);

        $this->getJson('/api/finance/investments')
            ->assertOk()
            ->assertJsonPath('portfolio.total_value', '0.00')
            ->assertJsonCount(0, 'data');

        $this->postJson('/api/finance/investments/' . Investment::query()->first()->id . '/transactions', [
            'type' => 'deposit',
            'amount' => 1000,
        ])->assertForbidden();
    }

    // =====================
    // REWARD WALLET TESTS
    // =====================

    public function test_reward_wallet_created_on_first_access(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $this->getJson('/api/finance/reward-wallet')
            ->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.available_balance', '0.00')
            ->assertJsonPath('data.locked_balance', '0.00');
    }

    public function test_reward_earn_via_service(): void
    {
        $user = User::factory()->create();
        $service = app(\App\Services\Finance\RewardWalletService::class);

        $wallet = $service->earnReward($user, 500, 'challenge', 'Completed 30 day challenge');

        $this->assertSame('500.00', $wallet->available_balance);

        $this->assertDatabaseHas('reward_transactions', [
            'user_id' => $user->id,
            'type' => 'earn',
            'amount' => 500,
            'source' => 'challenge',
        ]);
    }

    public function test_reward_spend_via_service(): void
    {
        $user = User::factory()->create();
        $service = app(\App\Services\Finance\RewardWalletService::class);

        $service->earnReward($user, 1000, 'achievement', 'Earned reward');
        $wallet = $service->spendReward($user, 300, 'Bought a gift');

        $this->assertSame('700.00', $wallet->available_balance);
    }

    public function test_reward_cannot_spend_more_than_balance(): void
    {
        $user = User::factory()->create();
        $service = app(\App\Services\Finance\RewardWalletService::class);

        $this->expectException(\RuntimeException::class);
        $service->spendReward($user, 100, 'Not enough');
    }

    public function test_reward_lock_and_unlock(): void
    {
        $user = User::factory()->create();
        $service = app(\App\Services\Finance\RewardWalletService::class);

        $service->earnReward($user, 1000, 'achievement', 'Test');
        $wallet = $service->lockReward($user, 400);

        $this->assertSame('600.00', $wallet->available_balance);
        $this->assertSame('400.00', $wallet->locked_balance);

        $wallet = $service->unlockReward($user, 400);

        $this->assertSame('1000.00', $wallet->available_balance);
        $this->assertSame('0.00', $wallet->locked_balance);
    }

    public function test_reward_transactions_history(): void
    {
        $user = User::factory()->create();
        $service = app(\App\Services\Finance\RewardWalletService::class);

        $service->earnReward($user, 1000, 'challenge', 'Completed 30 day challenge');
        $service->earnReward($user, 500, 'achievement', 'Reached milestone');
        $service->spendReward($user, 200, 'Bought coffee');

        Sanctum::actingAs($user);

        $this->getJson('/api/finance/reward-transactions')
            ->assertOk()
            ->assertJsonCount(3, 'data');
    }

    public function test_get_balance_returns_correct_amounts(): void
    {
        $user = User::factory()->create();
        $service = app(\App\Services\Finance\RewardWalletService::class);

        $service->earnReward($user, 2000, 'achievement', 'Test');
        $balance = $service->getBalance($user);

        $this->assertSame('2000.00', $balance['available_balance']);
        $this->assertSame('0.00', $balance['locked_balance']);
    }

    public function test_user_isolation_on_reward_wallet(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $service = app(\App\Services\Finance\RewardWalletService::class);

        $service->earnReward($user1, 5000, 'challenge', 'User 1 reward');

        Sanctum::actingAs($user2);

        $this->getJson('/api/finance/reward-wallet')
            ->assertOk()
            ->assertJsonPath('data.available_balance', '0.00');
    }

    // =====================
    // HELPERS
    // =====================

    private function expenseCategory(?string $name = null): ExpenseCategory
    {
        return ExpenseCategory::query()->create([
            'user_id' => null,
            'name' => $name ?? 'Food',
        ]);
    }

    private function makeIncome(User $user): Income
    {
        $source = IncomeSource::query()->create(['name' => 'Salary', 'is_default' => true]);

        return Income::query()->create([
            'user_id' => $user->id,
            'income_source_id' => $source->id,
            'amount' => 50000,
            'currency' => 'ETB',
            'income_date' => '2026-07-08',
        ]);
    }
}
