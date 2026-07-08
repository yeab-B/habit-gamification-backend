<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Challenge;
use App\Models\Friendship;
use App\Models\Task;
use App\Policies\CategoryPolicy;
use App\Policies\ChallengePolicy;
use App\Policies\FriendshipPolicy;
use App\Policies\TaskPolicy;
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
    }
}
