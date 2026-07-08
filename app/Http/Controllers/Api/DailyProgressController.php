<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Task\CompleteTaskRequest;
use App\Http\Resources\DailyCheckinResource;
use App\Http\Resources\TaskCompletionResource;
use App\Models\Task;
use App\Services\DailyProgressService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class DailyProgressController extends Controller
{
    public function __construct(private readonly DailyProgressService $dailyProgressService)
    {
    }

    public function completeTask(CompleteTaskRequest $request, Task $task): JsonResponse
    {
        try {
            $completion = $this->dailyProgressService->completeTask($request->user(), $task);
        } catch (RuntimeException $exception) {
            return response()->json([
                'status' => false,
                'message' => $exception->getMessage(),
            ], 422);
        }

        return response()->json([
            'status' => true,
            'message' => 'Task completed successfully',
            'data' => new TaskCompletionResource($completion),
        ]);
    }

    public function checkins(Request $request): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => 'Daily check-ins retrieved successfully',
            'data' => DailyCheckinResource::collection($this->dailyProgressService->getCheckins($request->user())),
        ]);
    }

    public function today(Request $request): JsonResponse
    {
        $summary = $this->dailyProgressService->getTodaySummary($request->user());

        return response()->json([
            'status' => true,
            'message' => 'Today\'s progress retrieved successfully',
            'data' => [
                'date' => $summary['date'],
                'completed_tasks' => $summary['completed_tasks'],
                'remaining_tasks' => $summary['remaining_tasks'],
                'earned_points' => $summary['earned_points'],
                'completion_percentage' => $summary['completion_percentage'],
                'daily_checkin' => $summary['daily_checkin'] ? new DailyCheckinResource($summary['daily_checkin']) : null,
            ],
        ]);
    }
}
