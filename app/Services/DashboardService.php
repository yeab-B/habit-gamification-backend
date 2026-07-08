<?php

namespace App\Services;

use App\Models\CoinTransaction;
use App\Models\Task;
use App\Models\User;
use App\Models\UserAchievement;
use Carbon\CarbonImmutable;

class DashboardService
{
    public function __construct(
        private readonly ChallengeService $challengeService,
        private readonly DailyProgressService $dailyProgressService,
        private readonly StreakService $streakService,
        private readonly CoinService $coinService,
        private readonly AchievementService $achievementService,
        private readonly StatisticsService $statisticsService
    ) {
    }

    public function dashboard(User $user): array
    {
        $today = $this->dailyProgressService->getTodaySummary($user);
        $streak = $this->streakService->getStatistics($user);
        $challenge = $this->challengeService->getCurrentActiveChallenge($user);

        return [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'avatar' => $user->avatar,
                'member_since' => $user->created_at?->toDateString(),
            ],
            'current_challenge' => $challenge ? [
                'challenge_id' => $challenge->id,
                'title' => $challenge->title,
                'duration_days' => $challenge->duration_days,
                'progress_percentage' => $this->challengeProgressPercentage($challenge->start_date, $challenge->end_date),
                'start_date' => $challenge->start_date?->toDateString(),
                'end_date' => $challenge->end_date?->toDateString(),
            ] : null,
            'today' => [
                'total_tasks' => Task::query()
                    ->where('user_id', $user->id)
                    ->where('is_active', true)
                    ->count(),
                'completed_tasks' => $today['completed_tasks'],
                'remaining_tasks' => $today['remaining_tasks'],
                'earned_points' => $today['earned_points'],
                'completion_percentage' => $today['completion_percentage'],
            ],
            'coins' => $this->coinSummary($user),
            'streak' => [
                'current_streak' => $streak->current_streak,
                'longest_streak' => $streak->longest_streak,
                'last_completed_date' => $streak->last_completed_date?->toDateString(),
            ],
            'weekly_progress' => $this->statisticsService->weeklyProgress($user),
            'monthly_progress' => $this->statisticsService->monthlyProgress($user),
            'achievement_summary' => $this->achievementSummary($user),
        ];
    }

    private function coinSummary(User $user): array
    {
        return [
            'current_balance' => $this->coinService->getBalance($user),
            'earned_today' => $this->earnedCoinsBetween($user, CarbonImmutable::now()->startOfDay(), CarbonImmutable::now()->endOfDay()),
            'earned_this_week' => $this->earnedCoinsBetween($user, CarbonImmutable::now()->startOfWeek(), CarbonImmutable::now()->endOfWeek()),
            'earned_this_month' => $this->earnedCoinsBetween($user, CarbonImmutable::now()->startOfMonth(), CarbonImmutable::now()->endOfMonth()),
        ];
    }

    private function achievementSummary(User $user): array
    {
        $recent = $this->achievementService->userAchievements($user, 5);

        return [
            'total_achievements' => UserAchievement::query()
                ->where('user_id', $user->id)
                ->count(),
            'recently_unlocked' => $recent->items(),
            'total_reward_coins' => (int) UserAchievement::query()
                ->where('user_id', $user->id)
                ->sum('reward_coins'),
        ];
    }

    private function earnedCoinsBetween(User $user, CarbonImmutable $start, CarbonImmutable $end): int
    {
        return (int) CoinTransaction::query()
            ->where('user_id', $user->id)
            ->whereIn('type', ['earn', 'bonus'])
            ->whereBetween('created_at', [$start, $end])
            ->sum('amount');
    }

    private function challengeProgressPercentage($startDate, $endDate): float
    {
        if ($startDate === null || $endDate === null) {
            return 0;
        }

        $start = CarbonImmutable::parse($startDate)->startOfDay();
        $end = CarbonImmutable::parse($endDate)->startOfDay();
        $totalDays = max($start->diffInDays($end) + 1, 1);
        $elapsedDays = min(max($start->diffInDays(CarbonImmutable::now()->startOfDay()) + 1, 0), $totalDays);

        return round(($elapsedDays / $totalDays) * 100, 2);
    }
}
