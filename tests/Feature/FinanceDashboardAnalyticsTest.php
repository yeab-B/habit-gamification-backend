<?php

namespace Tests\Feature;

use App\Finance\Models\BudgetAllocation;
use App\Finance\Models\EmergencyFund;
use App\Finance\Models\Expense;
use App\Finance\Models\ExpenseCategory;
use App\Finance\Models\Investment;
use App\Finance\Models\InvestmentTransaction;
use App\Finance\Models\RewardWallet;
use App\Finance\Services\FinancialHealthService;
use App\Models\Income;
use App\Models\IncomeSource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class FinanceDashboardAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    // =====================
    // DASHBOARD TESTS
    // =====================

    public function test_user_can_view_finance_dashboard(): void
    {
        $user = User::factory()->create();
        $this->seedData($user);

        Sanctum::actingAs($user);

        $this->getJson('/api/finance/dashboard')
            ->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('message', 'Finance dashboard retrieved successfully')
            ->assertJsonStructure([
                'data' => [
                    'income', 'budget', 'expenses', 'savings',
                    'emergency_fund', 'investments', 'reward_wallet',
                    'goals', 'financial_health',
                ],
            ]);
    }

    public function test_dashboard_returns_income_summary(): void
    {
        $user = User::factory()->create();
        $source = IncomeSource::query()->create(['name' => 'Salary', 'is_default' => true]);

        Income::query()->create([
            'user_id' => $user->id,
            'income_source_id' => $source->id,
            'amount' => 50000,
            'currency' => 'ETB',
            'income_date' => now()->toDateString(),
        ]);

        Income::query()->create([
            'user_id' => $user->id,
            'income_source_id' => $source->id,
            'amount' => 30000,
            'currency' => 'ETB',
            'income_date' => now()->subMonths(2)->toDateString(),
        ]);

        Sanctum::actingAs($user);

        $this->getJson('/api/finance/dashboard')
            ->assertOk()
            ->assertJsonPath('data.income.monthly_income', '50000.00')
            ->assertJsonPath('data.income.total_income', '80000.00')
            ->assertJsonPath('data.income.top_source', 'Salary');
    }

    public function test_dashboard_returns_budget_summary(): void
    {
        $user = User::factory()->create();
        $income = $this->makeIncome($user, 50000);

        BudgetAllocation::query()->create([
            'user_id' => $user->id,
            'income_id' => $income->id,
            'month' => now()->format('Y-m'),
            'income_amount' => 50000,
            'asrat_amount' => 5000,
            'needs_amount' => 22500,
            'emergency_amount' => 9000,
            'investment_amount' => 9000,
            'reward_amount' => 4500,
        ]);

        Sanctum::actingAs($user);

        $this->getJson('/api/finance/dashboard')
            ->assertOk()
            ->assertJsonPath('data.budget.income', '50000.00')
            ->assertJsonPath('data.budget.asrat', '5000.00')
            ->assertJsonPath('data.budget.needs', '22500.00')
            ->assertJsonPath('data.budget.emergency', '9000.00')
            ->assertJsonPath('data.budget.investment', '9000.00')
            ->assertJsonPath('data.budget.reward', '4500.00');
    }

    public function test_dashboard_returns_expense_analytics(): void
    {
        $user = User::factory()->create();
        $category = ExpenseCategory::query()->create(['name' => 'Food']);

        Expense::query()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'amount' => 3000,
            'expense_date' => now()->toDateString(),
        ]);

        Expense::query()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'amount' => 2000,
            'expense_date' => now()->toDateString(),
        ]);

        Sanctum::actingAs($user);

        $this->getJson('/api/finance/dashboard')
            ->assertOk()
            ->assertJsonPath('data.expenses.total_expense', '5000.00')
            ->assertJsonPath('data.expenses.top_category', 'Food');
    }

    public function test_dashboard_returns_emergency_fund_progress(): void
    {
        $user = User::factory()->create();

        EmergencyFund::query()->create([
            'user_id' => $user->id,
            'goal_amount' => 100000,
            'current_amount' => 40000,
            'status' => 'active',
        ]);

        Sanctum::actingAs($user);

        $this->getJson('/api/finance/dashboard')
            ->assertOk()
            ->assertJsonPath('data.emergency_fund.goal', '100000.00')
            ->assertJsonPath('data.emergency_fund.saved', '40000.00')
            ->assertJsonPath('data.emergency_fund.progress', 40);
    }

    public function test_dashboard_returns_investment_data(): void
    {
        $user = User::factory()->create();

        $investment = Investment::query()->create([
            'user_id' => $user->id,
            'name' => 'Stocks',
            'type' => 'Stocks',
            'total_amount' => 50000,
        ]);

        InvestmentTransaction::query()->create([
            'investment_id' => $investment->id,
            'type' => 'profit',
            'amount' => 5000,
            'date' => now()->toDateString(),
        ]);

        Sanctum::actingAs($user);

        $this->getJson('/api/finance/dashboard')
            ->assertOk()
            ->assertJsonPath('data.investments.total_investment', '50000.00')
            ->assertJsonPath('data.investments.profit', '5000.00');
    }

    public function test_dashboard_returns_reward_wallet(): void
    {
        $user = User::factory()->create();
        $service = app(\App\Finance\Services\RewardWalletService::class);

        $service->earnReward($user, 5000, 'challenge', 'Test reward');

        Sanctum::actingAs($user);

        $this->getJson('/api/finance/dashboard')
            ->assertOk()
            ->assertJsonPath('data.reward_wallet.total_earned', '5000.00')
            ->assertJsonPath('data.reward_wallet.available', '5000.00');
    }

    public function test_dashboard_returns_financial_health(): void
    {
        $user = User::factory()->create();
        $this->seedData($user);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/finance/dashboard');

        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                'financial_health' => ['score', 'level', 'components', 'recommendations'],
            ],
        ]);

        $score = $response->json('data.financial_health.score');
        $this->assertIsInt($score);
        $this->assertGreaterThanOrEqual(0, $score);
        $this->assertLessThanOrEqual(100, $score);
    }

    // =====================
    // STATISTICS TESTS
    // =====================

    public function test_monthly_statistics_are_correct(): void
    {
        $user = User::factory()->create();
        $source = IncomeSource::query()->create(['name' => 'Salary', 'is_default' => true]);

        Income::query()->create([
            'user_id' => $user->id,
            'income_source_id' => $source->id,
            'amount' => 50000,
            'currency' => 'ETB',
            'income_date' => now()->toDateString(),
        ]);

        Sanctum::actingAs($user);

        $this->getJson('/api/finance/statistics?period=monthly')
            ->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonStructure([
                'data' => [
                    'income_trend', 'expense_trend', 'saving_trend',
                    'investment_trend', 'budget_accuracy',
                ],
            ]);
    }

    public function test_yearly_statistics_are_correct(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $this->getJson('/api/finance/statistics?period=yearly')
            ->assertOk()
            ->assertJsonPath('status', true);
    }

    public function test_weekly_statistics_are_correct(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $this->getJson('/api/finance/statistics?period=weekly')
            ->assertOk()
            ->assertJsonPath('status', true);
    }

    public function test_statistics_defaults_to_monthly(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $this->getJson('/api/finance/statistics')
            ->assertOk()
            ->assertJsonPath('status', true);
    }

    public function test_invalid_statistics_period_rejected(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $this->getJson('/api/finance/statistics?period=invalid')
            ->assertStatus(422)
            ->assertJsonValidationErrors(['period']);
    }

    // =====================
    // FINANCIAL HEALTH TESTS
    // =====================

    public function test_financial_health_score_is_calculated_correctly(): void
    {
        $user = User::factory()->create();
        $healthService = app(FinancialHealthService::class);

        $result = $healthService->calculate($user);

        $this->assertIsInt($result['score']);
        $this->assertGreaterThanOrEqual(0, $result['score']);
        $this->assertLessThanOrEqual(100, $result['score']);
        $this->assertContains($result['level'], ['Needs Improvement', 'Building', 'Healthy', 'Excellent']);
        $this->assertIsArray($result['recommendations']);
    }

    public function test_health_score_improves_with_data(): void
    {
        $emptyUser = User::factory()->create();
        $fundedUser = User::factory()->create();

        $source = IncomeSource::query()->create(['name' => 'Salary', 'is_default' => true]);

        Income::query()->create([
            'user_id' => $fundedUser->id,
            'income_source_id' => $source->id,
            'amount' => 50000,
            'currency' => 'ETB',
            'income_date' => now()->toDateString(),
        ]);

        EmergencyFund::query()->create([
            'user_id' => $fundedUser->id,
            'goal_amount' => 100000,
            'current_amount' => 100000,
            'status' => 'completed',
        ]);

        Investment::query()->create([
            'user_id' => $fundedUser->id,
            'name' => 'Test',
            'type' => 'Savings',
            'total_amount' => 50000,
        ]);

        $healthService = app(FinancialHealthService::class);
        $emptyResult = $healthService->calculate($emptyUser);
        $fundedResult = $healthService->calculate($fundedUser);

        $this->assertGreaterThan($emptyResult['score'], $fundedResult['score']);
    }

    // =====================
    // AUTHORIZATION TESTS
    // =====================

    public function test_user_cannot_access_other_users_dashboard(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $this->seedData($user1);

        Sanctum::actingAs($user2);

        $this->getJson('/api/finance/dashboard')
            ->assertOk()
            ->assertJsonPath('data.income.total_income', '0.00')
            ->assertJsonPath('data.income.monthly_income', '0.00');
    }

    public function test_unauthenticated_user_cannot_access_dashboard(): void
    {
        $this->getJson('/api/finance/dashboard')
            ->assertStatus(401);

        $this->getJson('/api/finance/statistics')
            ->assertStatus(401);
    }

    // =====================
    // HELPERS
    // =====================

    private function seedData(User $user): void
    {
        $source = IncomeSource::query()->create(['name' => 'Salary', 'is_default' => true]);
        $category = ExpenseCategory::query()->create(['name' => 'Food']);

        Income::query()->create([
            'user_id' => $user->id,
            'income_source_id' => $source->id,
            'amount' => 50000,
            'currency' => 'ETB',
            'income_date' => now()->toDateString(),
        ]);

        Expense::query()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'amount' => 15000,
            'expense_date' => now()->toDateString(),
        ]);

        $income = $this->makeIncome($user, 50000);
        BudgetAllocation::query()->create([
            'user_id' => $user->id,
            'income_id' => $income->id,
            'month' => now()->format('Y-m'),
            'income_amount' => 50000,
            'asrat_amount' => 5000,
            'needs_amount' => 22500,
            'emergency_amount' => 9000,
            'investment_amount' => 9000,
            'reward_amount' => 4500,
        ]);

        EmergencyFund::query()->create([
            'user_id' => $user->id,
            'goal_amount' => 100000,
            'current_amount' => 40000,
            'status' => 'active',
        ]);

        Investment::query()->create([
            'user_id' => $user->id,
            'name' => 'Stocks',
            'type' => 'Stocks',
            'total_amount' => 50000,
        ]);

        $walletService = app(\App\Finance\Services\RewardWalletService::class);
        $walletService->earnReward($user, 3000, 'challenge', 'Test');
    }

    private function makeIncome(User $user, int $amount): Income
    {
        $source = IncomeSource::query()->create(['name' => 'Salary', 'is_default' => true]);

        return Income::query()->create([
            'user_id' => $user->id,
            'income_source_id' => $source->id,
            'amount' => $amount,
            'currency' => 'ETB',
            'income_date' => now()->toDateString(),
        ]);
    }
}
