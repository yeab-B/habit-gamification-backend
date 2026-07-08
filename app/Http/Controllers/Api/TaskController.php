<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Task\StoreTaskRequest;
use App\Http\Requests\Task\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use App\Services\TaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function __construct(private readonly TaskService $taskService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $tasks = $this->taskService->getTasks(
            $request->user(),
            $request->query('category_id')
        );

        return response()->json([
            'status' => true,
            'message' => 'Tasks retrieved successfully',
            'data' => TaskResource::collection($tasks),
        ]);
    }

    public function store(StoreTaskRequest $request): JsonResponse
    {
        $task = $this->taskService->createTask($request->user(), $request->validated());

        return response()->json([
            'status' => true,
            'message' => 'Task created successfully',
            'data' => new TaskResource($task),
        ], 201);
    }

    public function update(UpdateTaskRequest $request, Task $task): JsonResponse
    {
        $updatedTask = $this->taskService->updateTask($task, $request->validated());

        return response()->json([
            'status' => true,
            'message' => 'Task updated successfully',
            'data' => new TaskResource($updatedTask),
        ]);
    }

    public function destroy(Request $request, Task $task): JsonResponse
    {
        if (! $request->user()->can('delete', $task)) {
            return response()->json([
                'status' => false,
                'message' => 'This action is unauthorized.',
                'errors' => [
                    'authorization' => ['You are not allowed to perform this action.'],
                ],
            ], 403);
        }

        $this->taskService->deleteTask($task);

        return response()->json([
            'status' => true,
            'message' => 'Task deleted successfully',
            'data' => [],
        ]);
    }
}
