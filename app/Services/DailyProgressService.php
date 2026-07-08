<?php

namespace App\Services;

use App\Models\DailyCheckin;
use App\Models\Task;
use App\Models\TaskCompletion;
use App\Models\User;
use App\Http\Resources\DailyCheckinResource;
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

            return $completion->load(['task', 'user']);
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
            ->where('user_id', $user->id)
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
}
