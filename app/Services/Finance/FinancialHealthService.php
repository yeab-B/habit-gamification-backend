<?php

namespace App\Services\Finance;

use App\Models\Finance\BudgetAllocation;
use App\Models\Finance\EmergencyFund;
use App\Models\Finance\Expense;
use App\Models\Finance\Investment;
use App\Models\Finance\InvestmentTransaction;
use App\Models\User;
use Carbon\CarbonImmutable;

class FinancialHealthService
{
    private const WEIGHT_SAVINGS_RATE = 0.30;
    private const WEIGHT_EXPENSE_CONTROL = 0.25;
    private const WEIGHT_EMERGENCY_FUND = 0.20;
    private const WEIGHT_INVESTMENT_GROWTH = 0.15;
    private const WEIGHT_GOAL_COMPLETION = 0.10;

    public function calculate(User $user): array
    {
        $savingsRateScore = $this->savingsRateScore($user);
        $expenseControlScore = $this->expenseControlScore($user);
        $emergencyFundScore = $this->emergencyFundScore($user);
        $investmentGrowthScore = $this->investmentGrowthScore($user);
        $goalCompletionScore = $this->goalCompletionScore($user);

        $score = (int) round(
            ($savingsRateScore * self::WEIGHT_SAVINGS_RATE)
            + ($expenseControlScore * self::WEIGHT_EXPENSE_CONTROL)
            + ($emergencyFundScore * self::WEIGHT_EMERGENCY_FUND)
            + ($investmentGrowthScore * self::WEIGHT_INVESTMENT_GROWTH)
            + ($goalCompletionScore * self::WEIGHT_GOAL_COMPLETION)
        );

        return [
            'score' => min(100, max(0, $score)),
            'level' => $this->level($score),
            'components' => [
                'savings_rate' => $savingsRateScore,
                'expense_control' => $expenseControlScore,
                'emergency_fund' => $emergencyFundScore,
                'investment_growth' => $investmentGrowthScore,
                'goal_completion' => $goalCompletionScore,
            ],
            'recommendations' => $this->recommendations($savingsRateScore, $expenseControlScore, $emergencyFundScore, $investmentGrowthScore, $goalCompletionScore),
        ];
    }

    private function savingsRateScore(User $user): int
    {
        $monthStart = CarbonImmutable::now()->startOfMonth()->toDateString();
        $monthEnd = CarbonImmutable::now()->endOfMonth()->toDateString();

        $income = (float) BudgetAllocation::query()
            ->where('user_id', $user->id)
            ->where('month', now()->format('Y-m'))
            ->sum('income_amount');

        $expenses = (float) Expense::query()
            ->where('user_id', $user->id)
            ->whereBetween('expense_date', [$monthStart, $monthEnd])
            ->sum('amount');

        if ($income <= 0) {
            return 0;
        }

        $savingsRate = (($income - $expenses) / $income) * 100;

        return (int) min(100, max(0, $savingsRate));
    }

    private function expenseControlScore(User $user): int
    {
        $month = now()->format('Y-m');
        $allocation = (float) BudgetAllocation::query()
            ->where('user_id', $user->id)
            ->where('month', $month)
            ->sum('needs_amount');

        if ($allocation <= 0) {
            return 50;
        }

        $monthStart = CarbonImmutable::now()->startOfMonth()->toDateString();
        $monthEnd = CarbonImmutable::now()->endOfMonth()->toDateString();

        $spent = (float) Expense::query()
            ->where('user_id', $user->id)
            ->whereBetween('expense_date', [$monthStart, $monthEnd])
            ->sum('amount');

        $utilization = ($spent / $allocation) * 100;

        if ($utilization <= 50) {
            return 100;
        }
        if ($utilization <= 75) {
            return 80;
        }
        if ($utilization <= 100) {
            return 60;
        }

        return (int) max(0, 60 - ($utilization - 100));
    }

    private function emergencyFundScore(User $user): int
    {
        $funds = EmergencyFund::query()
            ->where('user_id', $user->id)
            ->get();

        if ($funds->isEmpty()) {
            return 0;
        }

        $totalProgress = $funds->sum(fn (EmergencyFund $fund) => $fund->goal_amount > 0
            ? ($fund->current_amount / $fund->goal_amount) * 100
            : 0
        );

        $averageProgress = $totalProgress / $funds->count();

        return (int) min(100, max(0, round($averageProgress)));
    }

    private function investmentGrowthScore(User $user): int
    {
        $profits = (float) InvestmentTransaction::query()
            ->whereHas('investment', fn ($query) => $query->where('user_id', $user->id))
            ->where('type', 'profit')
            ->sum('amount');

        $totalInvested = (float) Investment::query()
            ->where('user_id', $user->id)
            ->sum('total_amount');

        if ($totalInvested <= 0) {
            return $profits > 0 ? 50 : 0;
        }

        $growthRate = ($profits / $totalInvested) * 100;

        return (int) min(100, max(0, round($growthRate * 10)));
    }

    private function goalCompletionScore(User $user): int
    {
        $total = EmergencyFund::query()->where('user_id', $user->id)->count();
        $completed = EmergencyFund::query()->where('user_id', $user->id)->where('status', 'completed')->count();

        if ($total === 0) {
            return 0;
        }

        return (int) round(($completed / $total) * 100);
    }

    private function level(int $score): string
    {
        return match (true) {
            $score >= 91 => 'Excellent',
            $score >= 71 => 'Healthy',
            $score >= 41 => 'Building',
            default => 'Needs Improvement',
        };
    }

    private function recommendations(int $savingsRate, int $expenseControl, int $emergencyFund, int $investmentGrowth, int $goalCompletion): array
    {
        $recs = [];

        if ($savingsRate < 50) {
            $recs[] = 'Increase your savings rate by reducing non-essential expenses';
        }
        if ($expenseControl < 60) {
            $recs[] = 'Your expenses exceed your budget allocation - review your spending habits';
        }
        if ($emergencyFund < 50) {
            $recs[] = 'Build your emergency fund to at least 3-6 months of expenses';
        }
        if ($investmentGrowth < 30) {
            $recs[] = 'Consider diversifying your investments for better growth potential';
        }
        if ($goalCompletion < 50) {
            $recs[] = 'Focus on completing your active financial goals';
        }

        if (empty($recs)) {
            $recs[] = 'Excellent financial discipline - keep up the great work!';
        }

        return $recs;
    }
}
