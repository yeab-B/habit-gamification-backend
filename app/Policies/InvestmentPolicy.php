<?php

namespace App\Finance\Policies;

use App\Finance\Models\Investment;
use App\Models\User;

class InvestmentPolicy
{
    public function view(User $user, Investment $investment): bool
    {
        return $investment->user_id === $user->id;
    }
}
