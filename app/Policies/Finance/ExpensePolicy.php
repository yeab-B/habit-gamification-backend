<?php

namespace App\Policies\Finance;

use App\Models\Finance\Expense;
use App\Models\User;

class ExpensePolicy
{
    public function update(User $user, Expense $expense): bool
    {
        return $expense->user_id === $user->id;
    }

    public function delete(User $user, Expense $expense): bool
    {
        return $expense->user_id === $user->id;
    }
}
