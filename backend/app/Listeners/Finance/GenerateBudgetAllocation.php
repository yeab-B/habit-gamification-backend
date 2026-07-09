<?php

namespace App\Listeners\Finance;

use App\Events\IncomeCreated;
use App\Services\Finance\BudgetService;

class GenerateBudgetAllocation
{
    public function __construct(private readonly BudgetService $budgetService)
    {
    }

    public function handle(IncomeCreated $event): void
    {
        $this->budgetService->createMonthlyAllocation($event->income);
    }
}
