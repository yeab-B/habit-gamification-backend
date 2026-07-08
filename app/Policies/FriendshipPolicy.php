<?php

namespace App\Policies;

use App\Models\Friendship;
use App\Models\User;

class FriendshipPolicy
{
    public function view(User $user, Friendship $friendship): bool
    {
        return $this->isParticipant($user, $friendship);
    }

    public function accept(User $user, Friendship $friendship): bool
    {
        return $friendship->receiver_id === $user->id && $friendship->status === 'pending';
    }

    public function reject(User $user, Friendship $friendship): bool
    {
        return $friendship->receiver_id === $user->id && $friendship->status === 'pending';
    }

    public function remove(User $user, Friendship $friendship): bool
    {
        return $this->isParticipant($user, $friendship) && $friendship->status === 'accepted';
    }

    private function isParticipant(User $user, Friendship $friendship): bool
    {
        return $friendship->sender_id === $user->id || $friendship->receiver_id === $user->id;
    }
}
