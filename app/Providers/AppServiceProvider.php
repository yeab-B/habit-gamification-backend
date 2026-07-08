<?php

namespace App\Providers;

use App\Events\ChallengeCompleted;
use App\Events\CoinsEarned;
use App\Events\FreezeSent;
use App\Events\FriendRequestAccepted;
use App\Events\IncomeCreated;
use App\Events\PromiseFulfilled;
use App\Events\StreakMilestoneReached;
use App\Events\TaskCompleted;
use App\Finance\Events\BudgetAllocated;
use App\Finance\Events\EmergencyFundCompleted;
use App\Finance\Events\ExpenseCreated;
use App\Finance\Events\InvestmentCreated;
use App\Finance\Events\RewardEarned;
use App\Finance\Listeners\CheckFinanceAchievements;
use App\Finance\Listeners\GenerateBudgetAllocation;
use App\Finance\Listeners\UpdateFinanceStatistics;
use App\Finance\Models\EmergencyFund;
use App\Finance\Models\Expense;
use App\Finance\Models\Investment;
use App\Finance\Models\RewardWallet;
use App\Finance\Policies\ExpensePolicy;
use App\Finance\Policies\GoalPolicy;
use App\Finance\Policies\InvestmentPolicy;
use App\Finance\Policies\RewardWalletPolicy;
use App\Listeners\CheckChallengeAchievements;
use App\Listeners\CheckCoinAchievements;
use App\Listeners\CheckSocialAchievements;
use App\Listeners\CheckStreakAchievements;
use App\Listeners\CheckTaskAchievements;
use App\Models\Category;
use App\Models\Challenge;
use App\Models\Friendship;
use App\Models\Income;
use App\Models\Task;
use App\Policies\CategoryPolicy;
use App\Policies\ChallengePolicy;
use App\Policies\FriendshipPolicy;
use App\Policies\IncomePolicy;
use App\Policies\TaskPolicy;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Core policies
        Gate::policy(Category::class, CategoryPolicy::class);
        Gate::policy(Challenge::class, ChallengePolicy::class);
        Gate::policy(Friendship::class, FriendshipPolicy::class);
        Gate::policy(Income::class, IncomePolicy::class);
        Gate::policy(Task::class, TaskPolicy::class);

        // Finance policies
        Gate::policy(Expense::class, ExpensePolicy::class);
        Gate::policy(Investment::class, InvestmentPolicy::class);
        Gate::policy(EmergencyFund::class, GoalPolicy::class);
        Gate::policy(RewardWallet::class, RewardWalletPolicy::class);

        // Core event listeners
        Event::listen(TaskCompleted::class, CheckTaskAchievements::class);
        Event::listen(StreakMilestoneReached::class, CheckStreakAchievements::class);
        Event::listen(CoinsEarned::class, CheckCoinAchievements::class);
        Event::listen(ChallengeCompleted::class, CheckChallengeAchievements::class);
        Event::listen(FriendRequestAccepted::class, CheckSocialAchievements::class);
        Event::listen(FreezeSent::class, CheckSocialAchievements::class);
        Event::listen(PromiseFulfilled::class, CheckSocialAchievements::class);

        // Finance event listeners
        Event::listen(IncomeCreated::class, GenerateBudgetAllocation::class);
        Event::listen(BudgetAllocated::class, UpdateFinanceStatistics::class);
        Event::listen(ExpenseCreated::class, UpdateFinanceStatistics::class);
        Event::listen(EmergencyFundCompleted::class, UpdateFinanceStatistics::class);
        Event::listen(InvestmentCreated::class, UpdateFinanceStatistics::class);
        Event::listen(RewardEarned::class, UpdateFinanceStatistics::class);

        Event::listen(BudgetAllocated::class, CheckFinanceAchievements::class);
        Event::listen(EmergencyFundCompleted::class, CheckFinanceAchievements::class);
        Event::listen(InvestmentCreated::class, CheckFinanceAchievements::class);
        Event::listen(RewardEarned::class, CheckFinanceAchievements::class);
    }
}
