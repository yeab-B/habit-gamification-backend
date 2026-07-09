<?php

namespace App\Finance\Services;

use App\Finance\Models\BudgetAllocation;
use App\Finance\Models\EmergencyFund;
use App\Finance\Models\EmergencyTransaction;
use App\Finance\Models\Expense;
use App\Finance\Models\Investment;
use App\Finance\Models\InvestmentTransaction;
use App\Finance\Models\RewardTransaction;
use App\Models\Income;
use App\Models\User;
use Carbon\CarbonImmutable;

class FinanceReportService
{
    public function monthly(User $user, ?string $month = null): array
    {
        $month = $month ?? now()->format('Y-m');
        $start = CarbonImmutable::createFromFormat('Y-m', $month)->startOfMonth()->toDateString();
        $end = CarbonImmutable::createFromFormat('Y-m', $month)->endOfMonth()->toDateString();

        return $this->report($user, $start, $end, $month);
    }

    public function yearly(User $user, ?string $year = null): array
    {
        $year = $year ?? now()->format('Y');
        $start = CarbonImmutable::createFromFormat('Y', $year)->startOfYear()->toDateString();
        $end = CarbonImmutable::createFromFormat('Y', $year)->endOfYear()->toDateString();

        return $this->report($user, $start, $end, $year);
    }

    public function custom(User $user, string $startDate, string $endDate): array
    {
        return $this->report($user, $startDate, $endDate, 'custom');
    }

    private function report(User $user, string $start, string $end, string $period): array
    {
        $income = (float) Income::query()
            ->where('user_id', $user->id)
            ->whereBetween('income_date', [$start, $end])
            ->sum('amount');

        $expense = (float) Expense::query()
            ->where('user_id', $user->id)
            ->whereBetween('expense_date', [$start, $end])
            ->sum('amount');

        $expenseByCategory = Expense::query()
            ->where('user_id', $user->id)
            ->whereBetween('expense_date', [$start, $end])
            ->join('expense_categories', 'expenses.category_id', '=', 'expense_categories.id')
            ->groupBy('expense_categories.id', 'expense_categories.name')
            ->orderByDesc('total')
            ->limit(5)
            ->get([
                'expense_categories.name as category',
                \Illuminate\Support\Facades\DB::raw('COALESCE(SUM(expenses.amount), 0) as total'),
            ]);

        $allocations = BudgetAllocation::query()
            ->where('user_id', $user->id)
            ->whereBetween('month', [CarbonImmutable::parse($start)->format('Y-m'), CarbonImmutable::parse($end)->format('Y-m')])
            ->get();

        $emergencySaved = (float) EmergencyTransaction::query()
            ->whereHas('emergencyFund', fn ($query) => $query->where('user_id', $user->id))
            ->where('type', 'deposit')
            ->whereBetween('transaction_date', [$start, $end])
            ->sum('amount');

        $invested = (float) InvestmentTransaction::query()
            ->whereHas('investment', fn ($query) => $query->where('user_id', $user->id))
            ->whereIn('type', ['deposit', 'profit'])
            ->whereBetween('date', [$start, $end])
            ->sum('amount');

        $rewardEarned = (float) RewardTransaction::query()
            ->where('user_id', $user->id)
            ->where('type', 'earn')
            ->whereBetween('created_at', [$start, $end])
            ->sum('amount');

        return [
            'period' => $period,
            'date_range' => ['start' => $start, 'end' => $end],
            'income' => [
                'total' => number_format($income, 2, '.', ''),
                'asrat' => number_format($income * 0.10, 2, '.', ''),
                'remaining' => number_format($income * 0.90, 2, '.', ''),
            ],
            'expenses' => [
                'total' => number_format($expense, 2, '.', ''),
                'top_categories' => $expenseByCategory->map(fn ($item) => [
                    'category' => $item->category,
                    'total' => number_format((float) $item->total, 2, '.', ''),
                ]),
            ],
            'savings' => [
                'budgeted' => number_format($allocations->sum('emergency_amount') + $allocations->sum('investment_amount'), 2, '.', ''),
                'emergency_deposits' => number_format($emergencySaved, 2, '.', ''),
                'investments' => number_format($invested, 2, '.', ''),
            ],
            'budget_performance' => [
                'income_budgeted' => number_format($allocations->sum('income_amount'), 2, '.', ''),
                'needs_budgeted' => number_format($allocations->sum('needs_amount'), 2, '.', ''),
                'needs_spent' => number_format($expense, 2, '.', ''),
            ],
            'rewards' => [
                'earned' => number_format($rewardEarned, 2, '.', ''),
            ],
        ];
    }
}
