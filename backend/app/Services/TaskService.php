<?php

namespace App\Services;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class TaskService
{
    public function getTasks(User $user, ?string $categoryId = null): Collection
    {
        return Task::query()
            ->where('user_id', $user->id)
            ->when($categoryId, fn ($query) => $query->where('category_id', $categoryId))
            ->latest()
            ->get();
    }

    public function createTask(User $user, array $data): Task
    {
        return Task::query()->create([
            'user_id' => $user->id,
            'category_id' => $data['category_id'],
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'points' => $data['points'],
            'is_active' => true,
        ]);
    }

    public function updateTask(Task $task, array $data): Task
    {
        $task->fill($data);
        $task->save();

        return $task->fresh();
    }

    public function deleteTask(Task $task): void
    {
        $task->delete();
    }
}
