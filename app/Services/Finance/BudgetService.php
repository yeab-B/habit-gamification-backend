<?php

namespace App\Services\Finance;

use App\Events\Finance\BudgetAllocated;
use App\Models\Finance\BudgetAllocation;
use App\Models\Finance\BudgetSetting;
use App\Models\Income;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class BudgetService
{
    public function getSettings(User $user): BudgetSetting
    {
        return BudgetSetting::query()->firstOrCreate([
            'user_id' => $user->id,
        ], [
            'asrat_percentage' => 10,
            'needs_percentage' => 50,
            'emergency_percentage' => 20,
            'investment_percentage' => 20,
            'reward_percentage' => 10,
        ]);
    }

    public function updateSettings(User $user, array $data): BudgetSetting
    {
        $settings = $this->getSettings($user);
        $settings->fill($data)->save();

        return $settings->refresh();
    }

    public function calculateAsrat(float|string $amount, ?BudgetSetting $settings = null): array
    {
        $percentage = (float) ($settings?->asrat_percentage ?? 10);
        $income = round((float) $amount, 2);
        $asrat = round($income * ($percentage / 100), 2);

        return [
            'asrat' => $this->money($asrat),
            'remaining' => $this->money($income - $asrat),
        ];
    }

    public function calculateAllocation(float|string $amount, BudgetSetting $settings): array
    {
        $asrat = $this->calculateAsrat($amount, $settings);
        $remaining = (float) $asrat['remaining'];

        return [
            'income_amount' => $this->money($amount),
            'asrat_amount' => $asrat['asrat'],
            'needs_amount' => $this->money($remaining * ((float) $settings->needs_percentage / 100)),
            'emergency_amount' => $this->money($remaining * ((float) $settings->emergency_percentage / 100)),
            'investment_amount' => $this->money($remaining * ((float) $settings->investment_percentage / 100)),
            'reward_amount' => $this->money($remaining * ((float) $settings->reward_percentage / 100)),
        ];
    }

    public function createMonthlyAllocation(Income $income): BudgetAllocation
    {
        return DB::transaction(function () use ($income): BudgetAllocation {
            $settings = $this->getSettings($income->user);
            $allocation = $this->calculateAllocation($income->amount, $settings);

            $budgetAllocation = BudgetAllocation::query()->create([
                'user_id' => $income->user_id,
                'income_id' => $income->id,
                'month' => $income->income_date->format('Y-m'),
                ...$allocation,
            ]);

            BudgetAllocated::dispatch($budgetAllocation);

            return $budgetAllocation;
        });
    }

    public function updateAllocation(BudgetAllocation $allocation, array $data): BudgetAllocation
    {
        $allocation->fill($data)->save();

        return $allocation->refresh();
    }

    public function getBudget(User $user): array
    {
        $month = now()->format('Y-m');
        $allocations = BudgetAllocation::query()
            ->where('user_id', $user->id)
            ->where('month', $month)
            ->get();

        return [
            'settings' => $this->getSettings($user),
            'month' => $month,
            'totals' => [
                'income' => $this->money($allocations->sum('income_amount')),
                'asrat' => $this->money($allocations->sum('asrat_amount')),
                'needs' => $this->money($allocations->sum('needs_amount')),
                'emergency' => $this->money($allocations->sum('emergency_amount')),
                'investment' => $this->money($allocations->sum('investment_amount')),
                'reward' => $this->money($allocations->sum('reward_amount')),
            ],
            'allocations' => $allocations,
        ];
    }

    private function money(float|string $amount): string
    {
        return number_format(round((float) $amount, 2), 2, '.', '');
    }
}
