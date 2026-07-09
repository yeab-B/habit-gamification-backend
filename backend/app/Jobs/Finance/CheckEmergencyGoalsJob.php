<?php

namespace App\Jobs\Finance;

use App\Models\Finance\EmergencyFund;
use App\Services\Finance\EmergencyFundService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class CheckEmergencyGoalsJob implements ShouldQueue
{
    use Queueable;

    public function handle(EmergencyFundService $emergencyFundService): void
    {
        EmergencyFund::query()
            ->where('status', 'active')
            ->whereColumn('current_amount', '>=', 'goal_amount')
            ->each(function (EmergencyFund $fund) use ($emergencyFundService): void {
                $emergencyFundService->completeGoal($fund);
            });
    }
}
