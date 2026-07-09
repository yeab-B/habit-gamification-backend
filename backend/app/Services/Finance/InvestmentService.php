<?php

namespace App\Services\Finance;

use App\Events\Finance\InvestmentCreated;
use App\Models\Finance\Investment;
use App\Models\Finance\InvestmentTransaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class InvestmentService
{
    public function getPortfolio(User $user): array
    {
        $investments = Investment::query()
            ->with('transactions')
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        return [
            'total_value' => number_format($investments->sum('total_amount'), 2, '.', ''),
            'investments' => $investments,
        ];
    }

    public function createInvestment(User $user, array $data): Investment
    {
        $investment = Investment::query()->create([
            'user_id' => $user->id,
            'name' => $data['name'],
            'type' => $data['type'],
            'description' => $data['description'] ?? null,
            'total_amount' => $data['total_amount'] ?? 0,
        ]);

        InvestmentCreated::dispatch($investment);

        return $investment;
    }

    public function addTransaction(Investment $investment, array $data): Investment
    {
        return DB::transaction(function () use ($investment, $data): Investment {
            $amount = round((float) $data['amount'], 2);
            $type = $data['type'];
            $next = in_array($type, ['deposit', 'profit'], true)
                ? (float) $investment->total_amount + $amount
                : (float) $investment->total_amount - $amount;

            if ($next < 0) {
                throw new RuntimeException('Investment balance cannot go negative.');
            }

            InvestmentTransaction::query()->create([
                'investment_id' => $investment->id,
                'type' => $type,
                'amount' => $amount,
                'date' => $data['date'] ?? now()->toDateString(),
            ]);

            $investment->forceFill(['total_amount' => $next])->save();

            return $investment->refresh()->load('transactions');
        });
    }

    public function calculateGrowth(Investment $investment): array
    {
        $profits = (float) $investment->transactions()->where('type', 'profit')->sum('amount');
        $losses = (float) $investment->transactions()->where('type', 'loss')->sum('amount');

        return [
            'profit' => number_format($profits, 2, '.', ''),
            'loss' => number_format($losses, 2, '.', ''),
            'net_growth' => number_format($profits - $losses, 2, '.', ''),
        ];
    }
}
