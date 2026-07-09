<?php

namespace App\Services;

use App\Events\TaskCompleted;
use App\Models\DailyCheckin;
use App\Models\Task;
use App\Models\TaskCompletion;
use App\Models\User;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class DailyProgressService
{
    public function completeTask(User $user, Task $task): TaskCompletion
    {
        return DB::transaction(function () use ($user, $task): TaskCompletion {
            $today = now()->toDateString();

            $alreadyCompleted = TaskCompletion::query()
                ->where('user_id', $user->id)
                ->where('task_id', $task->id)
                ->whereDate('completion_date', $today)
                ->exists();

            if ($alreadyCompleted) {
                throw new RuntimeException('Task already completed today');
            }

            $completion = TaskCompletion::query()->create([
                'id' => (string) Str::uuid(),
                'user_id' => $user->id,
                'task_id' => $task->id,
                'completed_at' => now(),
                'completion_date' => $today,
            ]);

            $checkin = DailyCheckin::query()->firstOrCreate(
                [
                    'user_id' => $user->id,
                    'date' => $today,
                ],
                [
                    'id' => (string) Str::uuid(),
                    'tasks_completed' => 0,
                    'total_points' => 0,
                    'is_completed' => false,
                ]
            );

            $checkin->forceFill([
                'tasks_completed' => $checkin->tasks_completed + 1,
                'total_points' => $checkin->total_points + $task->points,
                'is_completed' => true,
            ])->save();

            $completion->load(['task', 'user']);

            TaskCompleted::dispatch($completion);

            return $completion;
        });
    }

    public function getCheckins(User $user): Collection
    {
        return DailyCheckin::query()
            ->where('user_id', $user->id)
            ->latest('date')
            ->get();
    }

    public function getTodaySummary(User $user): array
    {
        $today = now()->toDateString();

        $tasks = Task::query()
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->get();

        $completedCount = TaskCompletion::query()
            ->where('user_id', $user->id)
            ->whereDate('completion_date', $today)
            ->count();

        $earnedPoints = TaskCompletion::query()
            ->where('task_completions.user_id', $user->id)
            ->whereDate('completion_date', $today)
            ->join('tasks', 'task_completions.task_id', '=', 'tasks.id')
            ->sum('tasks.points');

        $checkin = DailyCheckin::query()
            ->where('user_id', $user->id)
            ->whereDate('date', $today)
            ->first();

        $remainingTasks = max($tasks->count() - $completedCount, 0);
        $completionPercentage = $tasks->count() > 0
            ? round(($completedCount / $tasks->count()) * 100, 2)
            : 0;

        return [
            'date' => $today,
            'completed_tasks' => $completedCount,
            'remaining_tasks' => $remainingTasks,
            'earned_points' => (int) $earnedPoints,
            'completion_percentage' => $completionPercentage,
                'daily_checkin' => $checkin,
        ];
    }

    public function hasCompletedRequiredProgress(User $user, CarbonInterface|string|null $date = null): bool
    {
        $date = $this->dateString($date ?? now());

        $requiredTaskCount = $this->requiredTaskCount($user);

        if ($requiredTaskCount === 0) {
            return false;
        }

        $completedCount = TaskCompletion::query()
            ->where('user_id', $user->id)
            ->whereDate('completion_date', $date)
            ->distinct('task_id')
            ->count('task_id');

        return $completedCount >= $requiredTaskCount;
    }

    public function hasRequiredProgress(User $user): bool
    {
        return $this->requiredTaskCount($user) > 0;
    }

    private function requiredTaskCount(User $user): int
    {
        return Task::query()
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->count();
    }

    private function dateString(CarbonInterface|string $date): string
    {
        if ($date instanceof CarbonInterface) {
            return $date->toDateString();
        }

        return CarbonImmutable::parse($date)->toDateString();
    }
}
