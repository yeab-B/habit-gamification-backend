<?php

namespace App\Jobs\Finance;

use App\Models\Finance\BudgetAllocation;
use App\Models\Finance\EmergencyFund;
use App\Models\Finance\Investment;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CalculateFinancialHealthJob implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly ?string $userId = null)
    {
    }

    public function handle(): void
    {
        $users = $this->userId !== null
            ? User::query()->where('id', $this->userId)->get()
            : User::query()->cursor();

        foreach ($users as $user) {
            $totalBudget = (float) BudgetAllocation::query()
                ->where('user_id', $user->id)
                ->sum('income_amount');

            $totalInvested = (float) Investment::query()
                ->where('user_id', $user->id)
                ->sum('total_amount');

            $emergencySaved = (float) EmergencyFund::query()
                ->where('user_id', $user->id)
                ->sum('current_amount');

            $totalSaved = $totalInvested + $emergencySaved;

            $healthScore = $totalBudget > 0
                ? min(100, round(($totalSaved / $totalBudget) * 100, 2))
                : 0;

            $health = [
                'total_budgeted' => $totalBudget,
                'total_saved' => $totalSaved,
                'investment_total' => $totalInvested,
                'emergency_total' => $emergencySaved,
                'health_score' => $healthScore,
            ];

            Cache::put("finance.health.{$user->id}", $health, now()->addDay());

            Log::info("Financial health calculated for user {$user->id}", $health);
        }
    }
}
