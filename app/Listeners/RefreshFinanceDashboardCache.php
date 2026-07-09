<?php

namespace App\Finance\Listeners;

use App\Finance\Events\BudgetAllocated;
use App\Finance\Events\EmergencyFundCompleted;
use App\Finance\Events\ExpenseCreated;
use App\Finance\Events\InvestmentCreated;
use App\Finance\Events\RewardEarned;
use Illuminate\Support\Facades\Cache;

class RefreshFinanceDashboardCache
{
    public function handle(
        BudgetAllocated|ExpenseCreated|EmergencyFundCompleted|InvestmentCreated|RewardEarned $event
    ): void {
        $user = match ($event::class) {
            BudgetAllocated::class => $event->allocation->user,
            ExpenseCreated::class => $event->expense->user,
            EmergencyFundCompleted::class => $event->fund->user,
            InvestmentCreated::class => $event->investment->user,
            RewardEarned::class => $event->wallet->user,
        };

        Cache::forget("finance.dashboard.{$user->id}");
        Cache::forget("finance.statistics.{$user->id}.weekly");
        Cache::forget("finance.statistics.{$user->id}.monthly");
        Cache::forget("finance.statistics.{$user->id}.yearly");
    }
}
