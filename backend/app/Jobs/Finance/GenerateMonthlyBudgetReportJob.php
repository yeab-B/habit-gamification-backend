<?php

namespace App\Jobs\Finance;

use App\Models\Finance\BudgetAllocation;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class GenerateMonthlyBudgetReportJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly ?string $month = null,
        private readonly ?string $userId = null,
    ) {
    }

    public function handle(): void
    {
        $month = $this->month ?? CarbonImmutable::now()->subMonth()->format('Y-m');

        $query = BudgetAllocation::query()->where('month', $month);

        if ($this->userId !== null) {
            $query->where('user_id', $this->userId);
        }

        $allocations = $query->get()->groupBy('user_id');

        foreach ($allocations as $userId => $userAllocations) {
            $totals = [
                'income' => $userAllocations->sum('income_amount'),
                'asrat' => $userAllocations->sum('asrat_amount'),
                'needs' => $userAllocations->sum('needs_amount'),
                'emergency' => $userAllocations->sum('emergency_amount'),
                'investment' => $userAllocations->sum('investment_amount'),
                'reward' => $userAllocations->sum('reward_amount'),
            ];

            Log::info("Monthly budget report for user {$userId} - {$month}", $totals);
        }
    }
}
