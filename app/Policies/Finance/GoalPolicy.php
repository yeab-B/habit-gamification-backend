<?php

namespace App\Policies\Finance;

use App\Models\Finance\EmergencyFund;
use App\Models\User;

class GoalPolicy
{
    public function view(User $user, EmergencyFund $fund): bool
    {
        return $fund->user_id === $user->id;
    }
}
