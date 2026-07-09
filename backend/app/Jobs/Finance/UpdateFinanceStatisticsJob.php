<?php

namespace App\Jobs\Finance;

use App\Models\Finance\BudgetAllocation;
use App\Models\Finance\EmergencyFund;
use App\Models\Finance\Expense;
use App\Models\Finance\Investment;
use App\Models\Finance\RewardTransaction;
use App\Models\Income;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class UpdateFinanceStatisticsJob implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly ?string $userId = null)
    {
    }

    public function handle(): void
    {
        $users = $this->userId !== null
            ? User::query()->where('id', $this->userId)->get()
            : User::query()->cursor();

        foreach ($users as $user) {
            $totalIncome = (float) Income::query()->where('user_id', $user->id)->sum('amount');
            $totalExpenses = (float) Expense::query()->where('user_id', $user->id)->sum('amount');
            $totalInvested = (float) Investment::query()->where('user_id', $user->id)->sum('total_amount');
            $emergencySaved = (float) EmergencyFund::query()->where('user_id', $user->id)->sum('current_amount');
            $rewardEarned = (float) RewardTransaction::query()->where('user_id', $user->id)->where('type', 'earn')->sum('amount');

            $statistics = [
                'total_income' => $totalIncome,
                'total_expenses' => $totalExpenses,
                'total_invested' => $totalInvested,
                'emergency_saved' => $emergencySaved,
                'reward_earned' => $rewardEarned,
                'calculated_at' => now()->toDateTimeString(),
            ];

            Cache::put("finance.stats.{$user->id}", $statistics, now()->addDay());

            Log::info("Finance statistics updated for user {$user->id}", $statistics);
        }
    }
}
