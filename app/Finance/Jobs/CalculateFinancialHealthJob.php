<?php

namespace App\Finance\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class CalculateFinancialHealthJob implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        //
    }
}
