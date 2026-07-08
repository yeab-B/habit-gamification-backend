<?php

namespace App\Finance\Listeners;

use App\Finance\Events\BudgetAllocated;
use App\Finance\Events\EmergencyFundCompleted;
use App\Finance\Events\ExpenseCreated;
use App\Finance\Events\InvestmentCreated;
use App\Finance\Events\RewardEarned;
use App\Finance\Models\BudgetAllocation;
use App\Finance\Models\Expense;
use App\Finance\Models\EmergencyFund;
use App\Finance\Models\Investment;
use Illuminate\Support\Facades\Cache;

class UpdateFinanceStatistics
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

        Cache::forget("finance.statistics.{$user->id}");
    }
}
