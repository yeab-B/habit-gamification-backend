<?php

namespace App\Services;

use App\Events\AchievementUnlocked;
use App\Models\Achievement;
use App\Models\Challenge;
use App\Models\CoinTransaction;
use App\Models\Freeze;
use App\Models\Friendship;
use App\Models\Promise;
use App\Models\TaskCompletion;
use App\Models\User;
use App\Models\UserAchievement;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class AchievementService
{
    public function __construct(private readonly CoinService $coinService)
    {
    }

    public function checkAchievements(User $user, ?string $conditionType = null): Collection
    {
        $achievements = Achievement::query()
            ->where('is_active', true)
            ->when($conditionType, fn ($query) => $query->where('condition_type', $conditionType))
            ->get();

        $unlocked = new Collection();

        foreach ($achievements as $achievement) {
            if ($this->metricValue($user, $achievement->condition_type) >= $achievement->condition_value) {
                $userAchievement = $this->unlock($user, $achievement);

                if ($userAchievement !== null) {
                    $unlocked->push($userAchievement);
                }
            }
        }

        return $unlocked;
    }

    public function unlock(User $user, Achievement $achievement): ?UserAchievement
    {
        if (! $achievement->is_active) {
            return null;
        }

        return DB::transaction(function () use ($user, $achievement): ?UserAchievement {
            $exists = UserAchievement::query()
                ->where('user_id', $user->id)
                ->where('achievement_id', $achievement->id)
                ->exists();

            if ($exists) {
                return null;
            }

            $userAchievement = UserAchievement::query()->create([
                'user_id' => $user->id,
                'achievement_id' => $achievement->id,
                'earned_at' => now(),
                'reward_coins' => $achievement->reward_coins,
            ])->load(['user', 'achievement']);

            if ($achievement->reward_coins > 0) {
                $this->coinService->bonusCoins(
                    $user,
                    'achievement',
                    $achievement->reward_coins,
                    'Achievement unlocked: ' . $achievement->name,
                    $achievement->id
                );
            }

            AchievementUnlocked::dispatch($userAchievement);

            return $userAchievement;
        });
    }

    public function userAchievements(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return UserAchievement::query()
            ->with('achievement')
            ->where('user_id', $user->id)
            ->latest('earned_at')
            ->paginate($perPage);
    }

    public function availableAchievements(): Collection
    {
        return Achievement::query()
            ->where('is_active', true)
            ->orderBy('category')
            ->orderBy('condition_value')
            ->get();
    }

    private function metricValue(User $user, string $conditionType): int
    {
        return match ($conditionType) {
            'tasks_completed' => TaskCompletion::query()
                ->where('user_id', $user->id)
                ->count(),
            'current_streak' => (int) ($user->streak()->value('current_streak') ?? 0),
            'coins_earned' => (int) CoinTransaction::query()
                ->where('user_id', $user->id)
                ->whereIn('type', ['earn', 'bonus'])
                ->sum('amount'),
            'challenges_completed' => $this->completedChallenges($user),
            'friends_count' => Friendship::query()
                ->where('status', 'accepted')
                ->where(function ($query) use ($user): void {
                    $query->where('sender_id', $user->id)
                        ->orWhere('receiver_id', $user->id);
                })
                ->count(),
            'freezes_sent' => Freeze::query()
                ->where('sender_id', $user->id)
                ->count(),
            'promises_fulfilled' => Promise::query()
                ->where('user_id', $user->id)
                ->where('status', 'fulfilled')
                ->count(),
            'budgets_allocated' => \App\Models\Finance\BudgetAllocation::query()
                ->where('user_id', $user->id)
                ->count(),
            'emergency_goals_completed' => \App\Models\Finance\EmergencyFund::query()
                ->where('user_id', $user->id)
                ->where('status', 'completed')
                ->count(),
            'investments_created' => \App\Models\Finance\Investment::query()
                ->where('user_id', $user->id)
                ->count(),
            'reward_earned' => (int) \App\Models\Finance\RewardTransaction::query()
                ->where('user_id', $user->id)
                ->where('type', 'earn')
                ->sum('amount'),
            default => 0,
        };
    }

    private function completedChallenges(User $user): int
    {
        $ownedCompleted = Challenge::query()
            ->where('user_id', $user->id)
            ->where('status', 'completed')
            ->count();

        $rewardedCompleted = CoinTransaction::query()
            ->where('user_id', $user->id)
            ->where('source', 'challenge_completion')
            ->distinct('reference_id')
            ->count('reference_id');

        return max($ownedCompleted, $rewardedCompleted);
    }
}
