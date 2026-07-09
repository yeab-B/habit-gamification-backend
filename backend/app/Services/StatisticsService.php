<?php

namespace App\Services;

use App\Models\Challenge;
use App\Models\CoinTransaction;
use App\Models\DailyCheckin;
use App\Models\Streak;
use App\Models\Task;
use App\Models\TaskCompletion;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class StatisticsService
{
    public function weeklyProgress(User $user): array
    {
        $start = CarbonImmutable::now()->subDays(6)->startOfDay();
        $end = CarbonImmutable::now()->endOfDay();

        $checkins = DailyCheckin::query()
            ->where('user_id', $user->id)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->get()
            ->keyBy(fn (DailyCheckin $checkin) => $checkin->date->toDateString());

        return collect(range(0, 6))
            ->map(function (int $offset) use ($start, $checkins): array {
                $date = $start->addDays($offset)->toDateString();
                $checkin = $checkins->get($date);

                return [
                    'date' => $date,
                    'completed_tasks' => $checkin?->tasks_completed ?? 0,
                    'points' => $checkin?->total_points ?? 0,
                ];
            })
            ->all();
    }

    public function monthlyProgress(User $user): array
    {
        $start = CarbonImmutable::now()->startOfMonth();
        $end = CarbonImmutable::now()->endOfMonth();

        $summary = DailyCheckin::query()
            ->where('user_id', $user->id)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->selectRaw('COUNT(*) as active_days, COALESCE(SUM(tasks_completed), 0) as completed_tasks, COALESCE(SUM(total_points), 0) as earned_points')
            ->first();

        $requiredTaskDays = Task::query()
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->count() * max(now()->day, 1);

        $completedTasks = (int) ($summary->completed_tasks ?? 0);

        return [
            'active_days' => (int) ($summary->active_days ?? 0),
            'completed_tasks' => $completedTasks,
            'earned_points' => (int) ($summary->earned_points ?? 0),
            'completion_percentage' => $requiredTaskDays > 0
                ? round(($completedTasks / $requiredTaskDays) * 100, 2)
                : 0,
        ];
    }

    public function report(User $user, string $period = 'monthly'): array
    {
        [$start, $end] = $this->periodRange($period);

        return [
            'period' => $period,
            'date_range' => [
                'start' => $start->toDateString(),
                'end' => $end->toDateString(),
            ],
            'completion_statistics' => $this->completionStatistics($user, $start, $end),
            'task_statistics' => $this->taskStatistics($user, $start, $end),
            'streak_statistics' => $this->streakStatistics($user),
            'coin_statistics' => $this->coinStatistics($user, $start, $end),
            'challenge_statistics' => $this->challengeStatistics($user, $start, $end),
        ];
    }

    private function completionStatistics(User $user, CarbonImmutable $start, CarbonImmutable $end): array
    {
        $checkins = DailyCheckin::query()
            ->where('user_id', $user->id)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->get();

        return [
            'active_days' => $checkins->count(),
            'completed_days' => $checkins->where('is_completed', true)->count(),
            'average_completion_percentage' => $this->averageCompletionPercentage($user, $checkins, $start, $end),
        ];
    }

    private function taskStatistics(User $user, CarbonImmutable $start, CarbonImmutable $end): array
    {
        return [
            'active_tasks' => Task::query()
                ->where('user_id', $user->id)
                ->where('is_active', true)
                ->count(),
            'completed_tasks' => TaskCompletion::query()
                ->where('user_id', $user->id)
                ->whereBetween('completion_date', [$start->toDateString(), $end->toDateString()])
                ->count(),
            'earned_points' => (int) TaskCompletion::query()
                ->where('task_completions.user_id', $user->id)
                ->whereBetween('completion_date', [$start->toDateString(), $end->toDateString()])
                ->join('tasks', 'task_completions.task_id', '=', 'tasks.id')
                ->sum('tasks.points'),
        ];
    }

    private function streakStatistics(User $user): array
    {
        $streak = Streak::query()->where('user_id', $user->id)->first();

        return [
            'current_streak' => $streak?->current_streak ?? 0,
            'longest_streak' => $streak?->longest_streak ?? 0,
            'last_completed_date' => $streak?->last_completed_date?->toDateString(),
        ];
    }

    private function coinStatistics(User $user, CarbonImmutable $start, CarbonImmutable $end): array
    {
        $credits = ['earn', 'bonus'];
        $debits = ['spend', 'penalty'];

        return [
            'earned' => (int) CoinTransaction::query()
                ->where('user_id', $user->id)
                ->whereIn('type', $credits)
                ->whereBetween('created_at', [$start, $end])
                ->sum('amount'),
            'spent_or_penalized' => (int) CoinTransaction::query()
                ->where('user_id', $user->id)
                ->whereIn('type', $debits)
                ->whereBetween('created_at', [$start, $end])
                ->sum('amount'),
            'transactions' => CoinTransaction::query()
                ->where('user_id', $user->id)
                ->whereBetween('created_at', [$start, $end])
                ->count(),
        ];
    }

    private function challengeStatistics(User $user, CarbonImmutable $start, CarbonImmutable $end): array
    {
        return [
            'active_challenges' => Challenge::query()
                ->where('status', 'active')
                ->where(function ($query) use ($user): void {
                    $query->where('user_id', $user->id)
                        ->orWhereHas('participants', fn ($query) => $query->where('users.id', $user->id));
                })
                ->count(),
            'completed_challenges' => Challenge::query()
                ->where('status', 'completed')
                ->where('user_id', $user->id)
                ->whereBetween('updated_at', [$start, $end])
                ->count(),
        ];
    }

    private function averageCompletionPercentage(User $user, Collection $checkins, CarbonImmutable $start, CarbonImmutable $end): float
    {
        $taskCount = Task::query()
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->count();

        if ($taskCount === 0) {
            return 0;
        }

        $days = max($start->diffInDays($end) + 1, 1);
        $possible = $taskCount * $days;
        $completed = $checkins->sum('tasks_completed');

        return round(($completed / $possible) * 100, 2);
    }

    private function periodRange(string $period): array
    {
        return match ($period) {
            'weekly' => [CarbonImmutable::now()->startOfWeek(), CarbonImmutable::now()->endOfWeek()],
            'yearly' => [CarbonImmutable::now()->startOfYear(), CarbonImmutable::now()->endOfYear()],
            default => [CarbonImmutable::now()->startOfMonth(), CarbonImmutable::now()->endOfMonth()],
        };
    }
}
