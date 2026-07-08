<?php

namespace App\Finance\Listeners;

use App\Finance\Events\BudgetAllocated;
use App\Finance\Events\EmergencyFundCompleted;
use App\Finance\Events\ExpenseCreated;
use App\Finance\Events\InvestmentCreated;
use App\Finance\Events\RewardEarned;
use App\Finance\Jobs\CalculateFinancialHealthScoreJob;
use Illuminate\Support\Facades\Cache;

class UpdateFinanceAnalytics
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

        Cache::forget("finance.health.{$user->id}");

        CalculateFinancialHealthScoreJob::dispatch($user->id);
    }
}
