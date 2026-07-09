<?php

namespace App\Events\Finance;

use App\Models\Finance\Expense;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ExpenseCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly Expense $expense)
    {
    }
}
