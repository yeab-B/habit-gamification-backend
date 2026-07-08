<?php

namespace App\Finance\Events;

use App\Finance\Models\BudgetAllocation;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BudgetAllocated
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly BudgetAllocation $allocation)
    {
    }
}
