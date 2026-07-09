<?php

namespace App\Events\Finance;

use App\Models\Finance\BudgetAllocation;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BudgetAllocated
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly BudgetAllocation $allocation)
    {
    }
}
