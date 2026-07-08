<?php

namespace App\Finance\Policies;

use App\Finance\Models\RewardWallet;
use App\Models\User;

class RewardWalletPolicy
{
    public function view(User $user, RewardWallet $wallet): bool
    {
        return $wallet->user_id === $user->id;
    }
}
