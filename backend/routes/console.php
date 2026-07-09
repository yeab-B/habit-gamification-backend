<?php

use App\Jobs\Finance\CalculateFinancialHealthScoreJob;
use App\Jobs\Finance\CheckEmergencyGoalsJob;
use App\Jobs\Finance\GenerateMonthlyBudgetReportJob;
use App\Jobs\Finance\GenerateMonthlyFinanceReportJob;
use App\Jobs\Finance\UpdateFinanceStatisticsJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::job(new UpdateFinanceStatisticsJob)->daily();
Schedule::job(new CheckEmergencyGoalsJob)->daily();
Schedule::call(function (): void {
    \App\Models\User::query()->each(function (\App\Models\User $user): void {
        \App\Jobs\Finance\CalculateFinancialHealthScoreJob::dispatch($user);
    });
})->daily()->name('calculate-financial-health');
Schedule::job(new GenerateMonthlyBudgetReportJob)->monthly();
Schedule::job(new GenerateMonthlyFinanceReportJob)->monthly();
