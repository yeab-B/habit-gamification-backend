<?php

namespace App\Finance\Events;

use App\Finance\Models\Expense;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ExpenseCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly Expense $expense)
    {
    }
}
