<?php

namespace App\Services\Finance;

use App\Models\Finance\BudgetAllocation;
use App\Models\Finance\EmergencyFund;
use App\Models\Finance\Expense;
use App\Models\Finance\ExpenseCategory;
use App\Models\Finance\Investment;
use App\Models\Finance\InvestmentTransaction;
use App\Models\Finance\RewardTransaction;
use App\Models\Income;
use App\Models\IncomeSource;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class FinanceDashboardService
{
    public function __construct(
        private readonly BudgetService $budgetService,
        private readonly FinancialHealthService $financialHealthService,
    ) {
    }

    public function dashboard(User $user): array
    {
        return Cache::remember("finance.dashboard.{$user->id}", 1800, function () use ($user): array {
            return [
                'income' => $this->incomeSummary($user),
                'budget' => $this->budgetSummary($user),
                'expenses' => $this->expenseAnalytics($user),
                'savings' => $this->savingAnalytics($user),
                'emergency_fund' => $this->emergencyFundAnalytics($user),
                'investments' => $this->investmentAnalytics($user),
                'reward_wallet' => $this->rewardWalletAnalytics($user),
                'goals' => $this->financialGoalsAnalytics($user),
                'financial_health' => $this->financialHealthService->calculate($user),
            ];
        });
    }

    public function statistics(User $user, string $period = 'monthly'): array
    {
        $cacheKey = "finance.statistics.{$user->id}.{$period}";

        return Cache::remember($cacheKey, 1800, function () use ($user, $period): array {
            return match ($period) {
                'weekly' => $this->weeklyStatistics($user),
                'yearly' => $this->yearlyStatistics($user),
                default => $this->monthlyStatistics($user),
            };
        });
    }

    private function incomeSummary(User $user): array
    {
        $month = now()->format('Y-m');
        $monthStart = CarbonImmutable::now()->startOfMonth()->toDateString();
        $monthEnd = CarbonImmutable::now()->endOfMonth()->toDateString();

        $monthlyIncome = (float) Income::query()
            ->where('user_id', $user->id)
            ->whereBetween('income_date', [$monthStart, $monthEnd])
            ->sum('amount');

        $totalIncome = (float) Income::query()
            ->where('user_id', $user->id)
            ->sum('amount');

        $topSource = IncomeSource::query()
            ->join('incomes', 'income_sources.id', '=', 'incomes.income_source_id')
            ->where('incomes.user_id', $user->id)
            ->groupBy('income_sources.id', 'income_sources.name')
            ->orderByDesc(\Illuminate\Support\Facades\DB::raw('SUM(incomes.amount)'))
            ->value('income_sources.name');

        $lastMonthStart = CarbonImmutable::now()->subMonth()->startOfMonth()->toDateString();
        $lastMonthEnd = CarbonImmutable::now()->subMonth()->endOfMonth()->toDateString();

        $lastMonthIncome = (float) Income::query()
            ->where('user_id', $user->id)
            ->whereBetween('income_date', [$lastMonthStart, $lastMonthEnd])
            ->sum('amount');

        $growth = $lastMonthIncome > 0
            ? round((($monthlyIncome - $lastMonthIncome) / $lastMonthIncome) * 100, 2)
            : 0;

        return [
            'monthly_income' => number_format($monthlyIncome, 2, '.', ''),
            'total_income' => number_format($totalIncome, 2, '.', ''),
            'top_source' => $topSource,
            'growth_percentage' => $growth,
            'month' => $month,
        ];
    }

    private function budgetSummary(User $user): array
    {
        $budget = $this->budgetService->getBudget($user);

        return [
            'income' => $budget['totals']['income'],
            'asrat' => $budget['totals']['asrat'],
            'needs' => $budget['totals']['needs'],
            'emergency' => $budget['totals']['emergency'],
            'investment' => $budget['totals']['investment'],
            'reward' => $budget['totals']['reward'],
        ];
    }

    private function expenseAnalytics(User $user): array
    {
        $monthStart = CarbonImmutable::now()->startOfMonth()->toDateString();
        $monthEnd = CarbonImmutable::now()->endOfMonth()->toDateString();

        $totalExpense = (float) Expense::query()
            ->where('user_id', $user->id)
            ->whereBetween('expense_date', [$monthStart, $monthEnd])
            ->sum('amount');

        $topCategory = ExpenseCategory::query()
            ->join('expenses', 'expense_categories.id', '=', 'expenses.category_id')
            ->where('expenses.user_id', $user->id)
            ->whereBetween('expenses.expense_date', [$monthStart, $monthEnd])
            ->groupBy('expense_categories.id', 'expense_categories.name')
            ->orderByDesc(\Illuminate\Support\Facades\DB::raw('SUM(expenses.amount)'))
            ->value('expense_categories.name');

        $needsAllocation = (float) BudgetAllocation::query()
            ->where('user_id', $user->id)
            ->where('month', now()->format('Y-m'))
            ->sum('needs_amount');

        $utilization = $needsAllocation > 0
            ? round(($totalExpense / $needsAllocation) * 100, 2)
            : 0;

        return [
            'total_expense' => number_format($totalExpense, 2, '.', ''),
            'needs_used_percentage' => $utilization,
            'top_category' => $topCategory,
        ];
    }

    private function savingAnalytics(User $user): array
    {
        $monthStart = CarbonImmutable::now()->startOfMonth()->toDateString();
        $monthEnd = CarbonImmutable::now()->endOfMonth()->toDateString();

        $monthlyIncome = (float) Income::query()
            ->where('user_id', $user->id)
            ->whereBetween('income_date', [$monthStart, $monthEnd])
            ->sum('amount');

        $monthlyExpense = (float) Expense::query()
            ->where('user_id', $user->id)
            ->whereBetween('expense_date', [$monthStart, $monthEnd])
            ->sum('amount');

        $saved = $monthlyIncome - $monthlyExpense;
        $savingRate = $monthlyIncome > 0
            ? round(($saved / $monthlyIncome) * 100, 2)
            : 0;

        return [
            'saved' => number_format(max(0, $saved), 2, '.', ''),
            'saving_rate' => max(0, $savingRate),
        ];
    }

    private function emergencyFundAnalytics(User $user): array
    {
        $funds = EmergencyFund::query()
            ->where('user_id', $user->id)
            ->get();

        $totalGoal = $funds->sum('goal_amount');
        $totalSaved = $funds->sum('current_amount');
        $progress = $totalGoal > 0
            ? round(($totalSaved / $totalGoal) * 100, 2)
            : 0;

        return [
            'goal' => number_format($totalGoal, 2, '.', ''),
            'saved' => number_format($totalSaved, 2, '.', ''),
            'progress' => $progress,
        ];
    }

    private function investmentAnalytics(User $user): array
    {
        $investments = Investment::query()
            ->where('user_id', $user->id)
            ->get();

        $totalInvestment = $investments->sum('total_amount');
        $investmentIds = $investments->pluck('id');

        $profits = (float) InvestmentTransaction::query()
            ->whereIn('investment_id', $investmentIds)
            ->where('type', 'profit')
            ->sum('amount');

        $losses = (float) InvestmentTransaction::query()
            ->whereIn('investment_id', $investmentIds)
            ->where('type', 'loss')
            ->sum('amount');

        $growth = $profits - $losses;

        return [
            'total_investment' => number_format($totalInvestment, 2, '.', ''),
            'growth' => number_format($growth, 2, '.', ''),
            'profit' => number_format($profits, 2, '.', ''),
            'loss' => number_format($losses, 2, '.', ''),
        ];
    }

    private function rewardWalletAnalytics(User $user): array
    {
        $wallet = \App\Models\Finance\RewardWallet::query()
            ->where('user_id', $user->id)
            ->first();

        if ($wallet === null) {
            return [
                'available' => '0.00',
                'locked' => '0.00',
                'total_earned' => '0.00',
                'total_spent' => '0.00',
            ];
        }

        $totalEarned = (float) RewardTransaction::query()
            ->where('user_id', $user->id)
            ->where('type', 'earn')
            ->sum('amount');

        $totalSpent = (float) RewardTransaction::query()
            ->where('user_id', $user->id)
            ->where('type', 'spend')
            ->sum('amount');

        return [
            'available' => $wallet->available_balance,
            'locked' => $wallet->locked_balance,
            'total_earned' => number_format($totalEarned, 2, '.', ''),
            'total_spent' => number_format($totalSpent, 2, '.', ''),
        ];
    }

    private function financialGoalsAnalytics(User $user): array
    {
        $active = EmergencyFund::query()
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->count();

        $completed = EmergencyFund::query()
            ->where('user_id', $user->id)
            ->where('status', 'completed')
            ->count();

        $funds = EmergencyFund::query()
            ->where('user_id', $user->id)
            ->get();

        $averageProgress = $funds->count() > 0
            ? round($funds->avg(fn ($f) => $f->goal_amount > 0 ? ($f->current_amount / $f->goal_amount) * 100 : 0), 2)
            : 0;

        return [
            'active_goals' => $active,
            'completed_goals' => $completed,
            'average_progress' => $averageProgress,
        ];
    }

    private function weeklyStatistics(User $user): array
    {
        $end = CarbonImmutable::now();
        $start = $end->subDays(6);

        return $this->trendStatistics($user, $start->toDateString(), $end->toDateString(), 'daily');
    }

    private function monthlyStatistics(User $user): array
    {
        $end = CarbonImmutable::now();
        $start = $end->subMonths(5)->startOfMonth();

        return $this->trendStatistics($user, $start->toDateString(), $end->toDateString(), 'monthly');
    }

    private function yearlyStatistics(User $user): array
    {
        $end = CarbonImmutable::now();
        $start = $end->subYears(4)->startOfYear();

        return $this->trendStatistics($user, $start->toDateString(), $end->toDateString(), 'yearly');
    }

    private function dateFormatSql(string $column): string
    {
        $driver = DB::connection()->getDriverName();

        return match ($driver) {
            'pgsql' => "to_char($column, 'YYYY-MM')",
            'sqlite' => "strftime('%Y-%m', $column)",
            'mysql', 'mariadb' => "DATE_FORMAT($column, '%Y-%m')",
            default => "to_char($column, 'YYYY-MM')",
        };
    }

    private function trendStatistics(User $user, string $start, string $end, string $groupBy): array
    {
        return [
            'income_trend' => $this->incomeTrend($user, $start, $end, $groupBy),
            'expense_trend' => $this->expenseTrend($user, $start, $end, $groupBy),
            'saving_trend' => $this->savingTrend($user, $start, $end, $groupBy),
            'investment_trend' => $this->investmentTrend($user, $start, $end, $groupBy),
            'budget_accuracy' => $this->budgetAccuracy($user),
        ];
    }

    private function incomeTrend(User $user, string $start, string $end, string $groupBy): array
    {
        $incomes = Income::query()
            ->where('user_id', $user->id)
            ->whereBetween('income_date', [$start, $end])
            ->selectRaw($this->dateFormatSql('income_date') . ' as period')
            ->selectRaw('COALESCE(SUM(amount), 0) as total')
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        return $incomes->map(fn ($item) => [
            'period' => $item->period,
            'amount' => number_format((float) $item->total, 2, '.', ''),
        ])->toArray();
    }

    private function expenseTrend(User $user, string $start, string $end, string $groupBy): array
    {
        $expenses = Expense::query()
            ->where('user_id', $user->id)
            ->whereBetween('expense_date', [$start, $end])
            ->selectRaw($this->dateFormatSql('expense_date') . ' as period')
            ->selectRaw('COALESCE(SUM(amount), 0) as total')
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        return $expenses->map(fn ($item) => [
            'period' => $item->period,
            'amount' => number_format((float) $item->total, 2, '.', ''),
        ])->toArray();
    }

    private function savingTrend(User $user, string $start, string $end, string $groupBy): array
    {
        $incomes = Income::query()
            ->where('user_id', $user->id)
            ->whereBetween('income_date', [$start, $end])
            ->selectRaw($this->dateFormatSql('income_date') . ' as period')
            ->selectRaw('COALESCE(SUM(amount), 0) as total')
            ->groupBy('period')
            ->pluck('total', 'period');

        $expenses = Expense::query()
            ->where('user_id', $user->id)
            ->whereBetween('expense_date', [$start, $end])
            ->selectRaw($this->dateFormatSql('expense_date') . ' as period')
            ->selectRaw('COALESCE(SUM(amount), 0) as total')
            ->groupBy('period')
            ->pluck('total', 'period');

        $periods = $incomes->keys()->merge($expenses->keys())->unique()->sort();

        return $periods->map(fn ($period) => [
            'period' => $period,
            'saved' => number_format(
                max(0, (float) ($incomes[$period] ?? 0) - (float) ($expenses[$period] ?? 0)),
                2, '.', ''
            ),
        ])->values()->toArray();
    }

    private function investmentTrend(User $user, string $start, string $end, string $groupBy): array
    {
        $transactions = InvestmentTransaction::query()
            ->whereHas('investment', fn ($query) => $query->where('user_id', $user->id))
            ->whereBetween('date', [$start, $end])
            ->selectRaw($this->dateFormatSql('date') . ' as period')
            ->selectRaw("COALESCE(SUM(CASE WHEN type IN ('deposit','profit') THEN amount ELSE 0 END), 0) as inflows")
            ->selectRaw("COALESCE(SUM(CASE WHEN type IN ('withdraw','loss') THEN amount ELSE 0 END), 0) as outflows")
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        return $transactions->map(fn ($item) => [
            'period' => $item->period,
            'net' => number_format((float) $item->inflows - (float) $item->outflows, 2, '.', ''),
        ])->toArray();
    }

    private function budgetAccuracy(User $user): array
    {
        $month = now()->format('Y-m');
        $allocation = BudgetAllocation::query()
            ->where('user_id', $user->id)
            ->where('month', $month)
            ->sum('needs_amount');

        $monthStart = CarbonImmutable::now()->startOfMonth()->toDateString();
        $monthEnd = CarbonImmutable::now()->endOfMonth()->toDateString();

        $spent = (float) Expense::query()
            ->where('user_id', $user->id)
            ->whereBetween('expense_date', [$monthStart, $monthEnd])
            ->sum('amount');

        $accuracy = $allocation > 0
            ? round(100 - abs((($spent - $allocation) / $allocation) * 100), 2)
            : 0;

        return [
            'budgeted' => number_format($allocation, 2, '.', ''),
            'spent' => number_format($spent, 2, '.', ''),
            'accuracy_percentage' => max(0, $accuracy),
        ];
    }
}
