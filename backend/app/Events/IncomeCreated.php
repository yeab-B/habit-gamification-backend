<?php

namespace App\Events;

use App\Models\Income;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class IncomeCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly Income $income)
    {
    }
}
