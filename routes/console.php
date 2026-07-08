<?php

use App\Finance\Jobs\CalculateFinancialHealthScoreJob;
use App\Finance\Jobs\CheckEmergencyGoalsJob;
use App\Finance\Jobs\GenerateMonthlyBudgetReportJob;
use App\Finance\Jobs\GenerateMonthlyFinanceReportJob;
use App\Finance\Jobs\UpdateFinanceStatisticsJob;
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
        \App\Finance\Jobs\CalculateFinancialHealthScoreJob::dispatch($user->id);
    });
})->daily()->name('calculate-financial-health');
Schedule::job(new GenerateMonthlyBudgetReportJob)->monthly();
Schedule::job(new GenerateMonthlyFinanceReportJob)->monthly();
