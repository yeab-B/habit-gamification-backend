<?php

namespace App\Finance\Jobs;

use App\Finance\Models\EmergencyFund;
use App\Finance\Services\EmergencyFundService;
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
