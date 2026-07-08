<?php

namespace App\Finance\Services;

use App\Finance\Events\EmergencyFundCompleted;
use App\Finance\Models\EmergencyFund;
use App\Finance\Models\EmergencyTransaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class EmergencyFundService
{
    public function getFunds(User $user)
    {
        return EmergencyFund::query()
            ->with('transactions')
            ->where('user_id', $user->id)
            ->latest()
            ->get();
    }

    public function createFund(User $user, array $data): EmergencyFund
    {
        return EmergencyFund::query()->create([
            'user_id' => $user->id,
            'goal_amount' => $data['goal_amount'],
            'current_amount' => $data['current_amount'] ?? 0,
            'status' => 'active',
        ]);
    }

    public function deposit(EmergencyFund $fund, float|string $amount): EmergencyFund
    {
        return $this->move($fund, 'deposit', $amount);
    }

    public function withdraw(EmergencyFund $fund, float|string $amount): EmergencyFund
    {
        return $this->move($fund, 'withdraw', $amount);
    }

    public function calculateProgress(EmergencyFund $fund): float
    {
        if ((float) $fund->goal_amount <= 0) {
            return 0;
        }

        return round(((float) $fund->current_amount / (float) $fund->goal_amount) * 100, 2);
    }

    public function completeGoal(EmergencyFund $fund): EmergencyFund
    {
        if ($fund->status !== 'completed') {
            $fund->forceFill(['status' => 'completed'])->save();
            EmergencyFundCompleted::dispatch($fund);
        }

        return $fund->refresh();
    }

    private function move(EmergencyFund $fund, string $type, float|string $amount): EmergencyFund
    {
        return DB::transaction(function () use ($fund, $type, $amount): EmergencyFund {
            $amount = round((float) $amount, 2);
            $next = $type === 'deposit'
                ? (float) $fund->current_amount + $amount
                : (float) $fund->current_amount - $amount;

            if ($next < 0) {
                throw new RuntimeException('Emergency fund balance cannot go negative.');
            }

            EmergencyTransaction::query()->create([
                'emergency_fund_id' => $fund->id,
                'type' => $type,
                'amount' => $amount,
                'transaction_date' => now()->toDateString(),
            ]);

            $fund->forceFill(['current_amount' => $next])->save();

            if ($next >= (float) $fund->goal_amount) {
                return $this->completeGoal($fund);
            }

            return $fund->refresh();
        });
    }
}
