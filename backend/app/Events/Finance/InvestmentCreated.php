<?php

namespace App\Events\Finance;

use App\Models\Finance\Investment;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class InvestmentCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly Investment $investment)
    {
    }
}
