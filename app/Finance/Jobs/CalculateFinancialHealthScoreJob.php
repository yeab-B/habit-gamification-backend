<?php

namespace App\Finance\Jobs;

use App\Finance\Services\FinancialHealthService;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;

class CalculateFinancialHealthScoreJob implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly string $userId)
    {
    }

    public function handle(FinancialHealthService $healthService): void
    {
        $user = User::query()->find($this->userId);

        if ($user === null) {
            return;
        }

        $health = $healthService->calculate($user);

        Cache::put("finance.health.{$user->id}", $health, now()->addHour());
    }
}
