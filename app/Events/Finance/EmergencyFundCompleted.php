<?php

namespace App\Events\Finance;

use App\Models\Finance\EmergencyFund;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EmergencyFundCompleted
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly EmergencyFund $fund)
    {
    }
}
