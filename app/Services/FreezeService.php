<?php

namespace App\Services;

use App\Events\FreezeSent;
use App\Events\FreezeUsed;
use App\Models\Freeze;
use App\Models\Friendship;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class FreezeService
{
    public function __construct(private readonly CoinService $coinService)
    {
    }

    public function sendFreeze(User $sender, User $receiver, ?string $reason = null): Freeze
    {
        if ($sender->id === $receiver->id) {
            throw new RuntimeException('You cannot send a freeze to yourself.');
        }

        if (! $this->areFriends($sender, $receiver)) {
            throw new RuntimeException('You can only send freezes to accepted friends.');
        }

        $cost = (int) config('freeze.cost', 3);

        if ($this->coinService->getBalance($sender) < $cost) {
            throw new RuntimeException('Insufficient coin balance.');
        }

        return DB::transaction(function () use ($sender, $receiver, $reason, $cost): Freeze {
            $freeze = Freeze::query()->create([
                'sender_id' => $sender->id,
                'receiver_id' => $receiver->id,
                'status' => 'available',
                'cost' => $cost,
                'reason' => $reason,
            ])->load(['sender', 'receiver']);

            FreezeSent::dispatch($freeze);

            return $freeze->refresh()->load(['sender', 'receiver']);
        });
    }

    public function useFreeze(User $user): ?Freeze
    {
        return DB::transaction(function () use ($user): ?Freeze {
            $freeze = Freeze::query()
                ->where('receiver_id', $user->id)
                ->where('status', 'available')
                ->oldest()
                ->lockForUpdate()
                ->first();

            if ($freeze === null) {
                return null;
            }

            $freeze->forceFill([
                'status' => 'used',
                'used_at' => now(),
            ])->save();

            $freeze->refresh()->load(['sender', 'receiver']);

            FreezeUsed::dispatch($freeze);

            return $freeze;
        });
    }

    public function getHistory(User $user, ?string $type = null, ?string $status = null, int $perPage = 15): LengthAwarePaginator
    {
        return Freeze::query()
            ->with(['sender', 'receiver'])
            ->where(function (Builder $query) use ($user, $type): void {
                if ($type === 'sent') {
                    $query->where('sender_id', $user->id);

                    return;
                }

                if ($type === 'received') {
                    $query->where('receiver_id', $user->id);

                    return;
                }

                $query->where('sender_id', $user->id)
                    ->orWhere('receiver_id', $user->id);
            })
            ->when($status, fn (Builder $query) => $query->where('status', $status))
            ->latest()
            ->paginate($perPage);
    }

    public function availableFreezes(User $user): int
    {
        return Freeze::query()
            ->where('receiver_id', $user->id)
            ->where('status', 'available')
            ->count();
    }

    private function areFriends(User $firstUser, User $secondUser): bool
    {
        return Friendship::query()
            ->where('status', 'accepted')
            ->where(function (Builder $query) use ($firstUser, $secondUser): void {
                $query->where(function (Builder $query) use ($firstUser, $secondUser): void {
                    $query->where('sender_id', $firstUser->id)
                        ->where('receiver_id', $secondUser->id);
                })->orWhere(function (Builder $query) use ($firstUser, $secondUser): void {
                    $query->where('sender_id', $secondUser->id)
                        ->where('receiver_id', $firstUser->id);
                });
            })
            ->exists();
    }
}
