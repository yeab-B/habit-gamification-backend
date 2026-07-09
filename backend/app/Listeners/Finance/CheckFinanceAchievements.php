<?php

namespace App\Listeners\Finance;

use App\Events\Finance\BudgetAllocated;
use App\Events\Finance\EmergencyFundCompleted;
use App\Events\Finance\InvestmentCreated;
use App\Events\Finance\RewardEarned;
use App\Models\User;
use App\Services\AchievementService;

class CheckFinanceAchievements
{
    public function __construct(private readonly AchievementService $achievementService)
    {
    }

    public function handle(
        BudgetAllocated|EmergencyFundCompleted|InvestmentCreated|RewardEarned $event
    ): void {
        $user = match ($event::class) {
            BudgetAllocated::class => $event->allocation->user,
            EmergencyFundCompleted::class => $event->fund->user,
            InvestmentCreated::class => $event->investment->user,
            RewardEarned::class => $event->wallet->user,
        };

        $conditionType = match ($event::class) {
            BudgetAllocated::class => 'budgets_allocated',
            EmergencyFundCompleted::class => 'emergency_goals_completed',
            InvestmentCreated::class => 'investments_created',
            RewardEarned::class => 'reward_earned',
        };

        $this->achievementService->checkAchievements($user, $conditionType);
    }
}
