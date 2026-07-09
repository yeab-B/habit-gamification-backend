<?php

namespace App\Policies\Finance;

use App\Models\Finance\Investment;
use App\Models\User;

class InvestmentPolicy
{
    public function view(User $user, Investment $investment): bool
    {
        return $investment->user_id === $user->id;
    }
}
