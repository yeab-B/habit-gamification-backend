<?php

namespace App\Policies\Finance;

use App\Models\Finance\RewardWallet;
use App\Models\User;

class RewardWalletPolicy
{
    public function view(User $user, RewardWallet $wallet): bool
    {
        return $wallet->user_id === $user->id;
    }
}
