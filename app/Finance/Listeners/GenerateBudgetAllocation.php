<?php

namespace App\Finance\Listeners;

use App\Events\IncomeCreated;
use App\Finance\Services\BudgetService;

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
