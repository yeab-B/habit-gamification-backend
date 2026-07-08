<?php

namespace App\Providers;

use App\Events\ChallengeCompleted;
use App\Events\CoinsEarned;
use App\Events\FreezeSent;
use App\Events\FriendRequestAccepted;
use App\Events\PromiseFulfilled;
use App\Events\StreakMilestoneReached;
use App\Events\TaskCompleted;
use App\Listeners\CheckChallengeAchievements;
use App\Listeners\CheckCoinAchievements;
use App\Listeners\CheckSocialAchievements;
use App\Listeners\CheckStreakAchievements;
use App\Listeners\CheckTaskAchievements;
use App\Models\Category;
use App\Models\Challenge;
use App\Models\Friendship;
use App\Models\Task;
use App\Policies\CategoryPolicy;
use App\Policies\ChallengePolicy;
use App\Policies\FriendshipPolicy;
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
        Gate::policy(Category::class, CategoryPolicy::class);
        Gate::policy(Challenge::class, ChallengePolicy::class);
        Gate::policy(Friendship::class, FriendshipPolicy::class);
        Gate::policy(Task::class, TaskPolicy::class);

        Event::listen(TaskCompleted::class, CheckTaskAchievements::class);
        Event::listen(StreakMilestoneReached::class, CheckStreakAchievements::class);
        Event::listen(CoinsEarned::class, CheckCoinAchievements::class);
        Event::listen(ChallengeCompleted::class, CheckChallengeAchievements::class);
        Event::listen(FriendRequestAccepted::class, CheckSocialAchievements::class);
        Event::listen(FreezeSent::class, CheckSocialAchievements::class);
        Event::listen(PromiseFulfilled::class, CheckSocialAchievements::class);
    }
}
