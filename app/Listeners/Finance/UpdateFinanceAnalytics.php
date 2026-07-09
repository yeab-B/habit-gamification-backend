<?php

namespace App\Listeners\Finance;

use App\Events\Finance\BudgetAllocated;
use App\Events\Finance\EmergencyFundCompleted;
use App\Events\Finance\ExpenseCreated;
use App\Events\Finance\InvestmentCreated;
use App\Events\Finance\RewardEarned;
use App\Jobs\Finance\CalculateFinancialHealthScoreJob;
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
