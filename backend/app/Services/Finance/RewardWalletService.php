<?php

namespace App\Services\Finance;

use App\Events\Finance\RewardEarned;
use App\Models\Finance\RewardTransaction;
use App\Models\Finance\RewardWallet;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class RewardWalletService
{
    public function getWallet(User $user): RewardWallet
    {
        return RewardWallet::query()->firstOrCreate([
            'user_id' => $user->id,
        ], [
            'available_balance' => 0,
            'locked_balance' => 0,
        ]);
    }

    public function getTransactions(User $user): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return RewardTransaction::query()
            ->where('user_id', $user->id)
            ->latest()
            ->paginate(15);
    }

    public function earnReward(User $user, float|string $amount, string $source, ?string $description = null): RewardWallet
    {
        return DB::transaction(function () use ($user, $amount, $source, $description): RewardWallet {
            $wallet = $this->getWallet($user);
            $amount = round((float) $amount, 2);

            RewardTransaction::query()->create([
                'user_id' => $user->id,
                'reward_wallet_id' => $wallet->id,
                'type' => 'earn',
                'amount' => $amount,
                'source' => $source,
                'description' => $description,
            ]);

            $wallet->forceFill([
                'available_balance' => (float) $wallet->available_balance + $amount,
            ])->save();

            RewardEarned::dispatch($wallet, $amount);

            return $wallet->refresh();
        });
    }

    public function spendReward(User $user, float|string $amount, ?string $description = null): RewardWallet
    {
        return DB::transaction(function () use ($user, $amount, $description): RewardWallet {
            $wallet = $this->getWallet($user);
            $amount = round((float) $amount, 2);

            if ((float) $wallet->available_balance < $amount) {
                throw new RuntimeException('Reward wallet balance is insufficient.');
            }

            RewardTransaction::query()->create([
                'user_id' => $user->id,
                'reward_wallet_id' => $wallet->id,
                'type' => 'spend',
                'amount' => $amount,
                'source' => 'reward_wallet',
                'description' => $description,
            ]);

            $wallet->forceFill([
                'available_balance' => (float) $wallet->available_balance - $amount,
            ])->save();

            return $wallet->refresh();
        });
    }

    public function lockReward(User $user, float|string $amount): RewardWallet
    {
        $wallet = $this->getWallet($user);
        $amount = round((float) $amount, 2);

        if ((float) $wallet->available_balance < $amount) {
            throw new RuntimeException('Reward wallet balance is insufficient.');
        }

        $wallet->forceFill([
            'available_balance' => (float) $wallet->available_balance - $amount,
            'locked_balance' => (float) $wallet->locked_balance + $amount,
        ])->save();

        return $wallet->refresh();
    }

    public function unlockReward(User $user, float|string $amount): RewardWallet
    {
        $wallet = $this->getWallet($user);
        $amount = round((float) $amount, 2);

        $wallet->forceFill([
            'available_balance' => (float) $wallet->available_balance + $amount,
            'locked_balance' => max((float) $wallet->locked_balance - $amount, 0),
        ])->save();

        return $wallet->refresh();
    }

    public function getBalance(User $user): array
    {
        $wallet = $this->getWallet($user);

        return [
            'available_balance' => $wallet->available_balance,
            'locked_balance' => $wallet->locked_balance,
        ];
    }
}
