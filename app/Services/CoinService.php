<?php

namespace App\Services;

use App\Events\CoinsEarned;
use App\Models\CoinTransaction;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

class CoinService
{
    private const CREDIT_TYPES = ['earn', 'bonus'];

    public function earnCoins(User $user, string $source, int $amount, ?string $description = null, ?string $referenceId = null): CoinTransaction
    {
        return $this->createTransaction($user, 'earn', $source, $amount, $description, $referenceId);
    }

    public function spendCoins(User $user, string $source, int $amount, ?string $description = null, ?string $referenceId = null): CoinTransaction
    {
        return $this->createTransaction($user, 'spend', $source, $amount, $description, $referenceId);
    }

    public function bonusCoins(User $user, string $source, int $amount, ?string $description = null, ?string $referenceId = null): CoinTransaction
    {
        return $this->createTransaction($user, 'bonus', $source, $amount, $description, $referenceId);
    }

    public function penaltyCoins(User $user, string $source, int $amount, ?string $description = null, ?string $referenceId = null): CoinTransaction
    {
        return $this->createTransaction($user, 'penalty', $source, $amount, $description, $referenceId);
    }

    public function createTransaction(
        User $user,
        string $type,
        string $source,
        int $amount,
        ?string $description = null,
        ?string $referenceId = null
    ): CoinTransaction {
        if (! in_array($type, ['earn', 'spend', 'bonus', 'penalty'], true)) {
            throw new InvalidArgumentException('Invalid coin transaction type.');
        }

        if ($amount <= 0) {
            throw new InvalidArgumentException('Coin transaction amount must be positive.');
        }

        return DB::transaction(function () use ($user, $type, $source, $amount, $description, $referenceId): CoinTransaction {
            $currentBalance = $this->getBalance($user);
            $balanceAfter = in_array($type, self::CREDIT_TYPES, true)
                ? $currentBalance + $amount
                : $currentBalance - $amount;

            if ($balanceAfter < 0) {
                throw new RuntimeException('Insufficient coin balance.');
            }

            $transaction = CoinTransaction::query()->create([
                'user_id' => $user->id,
                'type' => $type,
                'source' => $source,
                'amount' => $amount,
                'balance_after' => $balanceAfter,
                'description' => $description,
                'reference_id' => $referenceId,
            ]);

            if (in_array($type, self::CREDIT_TYPES, true)) {
                CoinsEarned::dispatch($transaction->load('user'));
            }

            return $transaction;
        });
    }

    public function getBalance(User $user): int
    {
        $transaction = CoinTransaction::query()
            ->where('user_id', $user->id)
            ->latest('created_at')
            ->latest('id')
            ->first();

        return $transaction?->balance_after ?? 0;
    }

    public function getHistory(User $user, ?string $type = null, int $perPage = 15): LengthAwarePaginator
    {
        return CoinTransaction::query()
            ->where('user_id', $user->id)
            ->when($type, fn ($query) => $query->where('type', $type))
            ->latest()
            ->paginate($perPage);
    }
}
