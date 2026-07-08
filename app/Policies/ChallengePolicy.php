<?php

namespace App\Policies;

use App\Models\Challenge;
use App\Models\User;

class ChallengePolicy
{
    public function view(User $user, Challenge $challenge): bool
    {
        return true;
    }

    public function update(User $user, Challenge $challenge): bool
    {
        return $challenge->user_id === $user->id;
    }

    public function delete(User $user, Challenge $challenge): bool
    {
        return $challenge->user_id === $user->id;
    }

    public function manageCategories(User $user, Challenge $challenge): bool
    {
        return $challenge->user_id === $user->id;
    }

    public function join(User $user, Challenge $challenge): bool
    {
        return true;
    }

    public function leave(User $user, Challenge $challenge): bool
    {
        return true;
    }
}
