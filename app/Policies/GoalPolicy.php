<?php

namespace App\Finance\Policies;

use App\Finance\Models\EmergencyFund;
use App\Models\User;

class GoalPolicy
{
    public function view(User $user, EmergencyFund $fund): bool
    {
        return $fund->user_id === $user->id;
    }
}
