<?php

namespace App\Finance\Jobs;

use App\Finance\Services\FinanceReportService;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class GenerateMonthlyFinanceReportJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly ?string $userId = null,
        private readonly ?string $month = null,
    ) {
    }

    public function handle(FinanceReportService $reportService): void
    {
        $month = $this->month ?? CarbonImmutable::now()->subMonth()->format('Y-m');

        $users = $this->userId !== null
            ? User::query()->where('id', $this->userId)->get()
            : User::query()->cursor();

        foreach ($users as $user) {
            $report = $reportService->monthly($user, $month);

            Cache::put(
                "finance.report.{$user->id}.{$month}",
                $report,
                now()->addDays(7),
            );

            Log::info("Monthly finance report generated for user {$user->id}", [
                'month' => $month,
                'income' => $report['income']['total'],
                'expenses' => $report['expenses']['total'],
            ]);
        }
    }
}
