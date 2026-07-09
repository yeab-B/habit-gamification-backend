<?php

namespace App\Services;

use App\Events\FriendRemoved;
use App\Events\FriendRequestAccepted;
use App\Events\FriendRequestSent;
use App\Models\Friendship;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class FriendService
{
    public function __construct(private readonly CoinService $coinService)
    {
    }

    public function searchUsers(User $user, ?string $search): Collection
    {
        $relatedUserIds = Friendship::query()
            ->where(function ($query) use ($user): void {
                $query->where('sender_id', $user->id)
                    ->orWhere('receiver_id', $user->id);
            })
            ->get(['sender_id', 'receiver_id'])
            ->flatMap(fn (Friendship $friendship) => [$friendship->sender_id, $friendship->receiver_id])
            ->push($user->id)
            ->unique()
            ->values()
            ->all();

        return User::query()
            ->whereNotIn('id', $relatedUserIds)
            ->when($search, function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', '%' . $search . '%')
                        ->orWhere('email', 'like', '%' . $search . '%');
                });
            })
            ->latest()
            ->limit(20)
            ->get();
    }

    public function getFriends(User $user): Collection
    {
        $friendships = Friendship::query()
            ->with(['sender.streak', 'receiver.streak'])
            ->where('status', 'accepted')
            ->where(function ($query) use ($user): void {
                $query->where('sender_id', $user->id)
                    ->orWhere('receiver_id', $user->id);
            })
            ->latest('responded_at')
            ->get();

        $friendships->each(function (Friendship $friendship) use ($user): void {
            $friend = $this->friendFor($friendship, $user);
            $friend->setAttribute('coin_balance', $this->coinService->getBalance($friend));
        });

        return $friendships;
    }

    public function sendRequest(User $sender, User $receiver): Friendship
    {
        if ($sender->id === $receiver->id) {
            throw new RuntimeException('You cannot send a friend request to yourself.');
        }

        if ($this->hasActiveRelationship($sender, $receiver)) {
            throw new RuntimeException('A friendship or pending request already exists.');
        }

        return DB::transaction(function () use ($sender, $receiver): Friendship {
            $friendship = Friendship::query()->create([
                'sender_id' => $sender->id,
                'receiver_id' => $receiver->id,
                'status' => 'pending',
            ]);

            FriendRequestSent::dispatch($friendship->load(['sender', 'receiver']));

            return $friendship;
        });
    }

    public function accept(Friendship $friendship): Friendship
    {
        return DB::transaction(function () use ($friendship): Friendship {
            $friendship->forceFill([
                'status' => 'accepted',
                'responded_at' => now(),
            ])->save();

            FriendRequestAccepted::dispatch($friendship->load(['sender', 'receiver']));

            return $friendship->refresh();
        });
    }

    public function reject(Friendship $friendship): Friendship
    {
        return DB::transaction(function () use ($friendship): Friendship {
            $friendship->forceFill([
                'status' => 'rejected',
                'responded_at' => now(),
            ])->save();

            return $friendship->refresh();
        });
    }

    public function remove(Friendship $friendship): void
    {
        DB::transaction(function () use ($friendship): void {
            $friendship->delete();

            FriendRemoved::dispatch($friendship);
        });
    }

    public function friendFor(Friendship $friendship, User $user): User
    {
        return $friendship->sender_id === $user->id
            ? $friendship->receiver
            : $friendship->sender;
    }

    public function hasActiveRelationship(User $firstUser, User $secondUser): bool
    {
        return Friendship::query()
            ->whereIn('status', ['pending', 'accepted'])
            ->where(function ($query) use ($firstUser, $secondUser): void {
                $query->where(function ($query) use ($firstUser, $secondUser): void {
                    $query->where('sender_id', $firstUser->id)
                        ->where('receiver_id', $secondUser->id);
                })->orWhere(function ($query) use ($firstUser, $secondUser): void {
                    $query->where('sender_id', $secondUser->id)
                        ->where('receiver_id', $firstUser->id);
                });
            })
            ->exists();
    }
}
