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
use OpenApi\Attributes as OA;
use RuntimeException;

class DailyProgressController extends Controller
{
    public function __construct(private readonly DailyProgressService $dailyProgressService)
    {
    }

    #[OA\Post(
        path: "/tasks/{task}/complete",
        summary: "Mark task as completed",
        description: "Completes a task for today, awards points/coins, and updates progress.",
        security: [["bearerAuth" => []]],
        tags: ["Daily Progress"],
        parameters: [
            new OA\Parameter(
                name: "task",
                in: "path",
                required: true,
                description: "The ID of the task to complete",
                schema: new OA\Schema(type: "string", format: "uuid")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Task completed successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Task completed successfully"),
                        new OA\Property(
                            property: "data",
                            type: "object",
                            properties: [
                                new OA\Property(property: "id", type: "string", format: "uuid", example: "9b3d9d30-b9bd-474c-8822-67cc3b922b05"),
                                new OA\Property(property: "task_id", type: "string", format: "uuid", example: "9b3d9d30-b5bd-474c-8822-67cc3b922a96"),
                                new OA\Property(property: "points_awarded", type: "integer", example: 15),
                                new OA\Property(property: "completed_at", type: "string", format: "date-time", example: "2026-07-09T03:56:49.000000Z")
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Unauthenticated", content: new OA\JsonContent(ref: "#/components/schemas/UnauthorizedResponse")),
            new OA\Response(response: 404, description: "Task not found", content: new OA\JsonContent(ref: "#/components/schemas/NotFoundResponse")),
            new OA\Response(response: 422, description: "Validation/Business rule failure", content: new OA\JsonContent(ref: "#/components/schemas/ValidationErrorResponse"))
        ]
    )]
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

    #[OA\Get(
        path: "/daily-checkins",
        summary: "List daily checkins",
        description: "Retrieves a history of the user's daily login and streak checkins.",
        security: [["bearerAuth" => []]],
        tags: ["Daily Progress"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Daily check-ins retrieved successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Daily check-ins retrieved successfully"),
                        new OA\Property(
                            property: "data",
                            type: "array",
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: "id", type: "string", format: "uuid", example: "9b3d9d30-b9bd-474c-8822-67cc3b922b06"),
                                    new OA\Property(property: "checkin_date", type: "string", format: "date", example: "2026-07-09"),
                                    new OA\Property(property: "streak_count", type: "integer", example: 7)
                                ]
                            )
                        )
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Unauthenticated", content: new OA\JsonContent(ref: "#/components/schemas/UnauthorizedResponse"))
        ]
    )]
    public function checkins(Request $request): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => 'Daily check-ins retrieved successfully',
            'data' => DailyCheckinResource::collection($this->dailyProgressService->getCheckins($request->user())),
        ]);
    }

    #[OA\Get(
        path: "/today",
        summary: "Get today's progress summary",
        description: "Retrieves stats for today's completed and remaining tasks, earned points, and check-in status.",
        security: [["bearerAuth" => []]],
        tags: ["Daily Progress"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Today's progress retrieved successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Today's progress retrieved successfully"),
                        new OA\Property(
                            property: "data",
                            type: "object",
                            properties: [
                                new OA\Property(property: "date", type: "string", format: "date", example: "2026-07-09"),
                                new OA\Property(property: "completed_tasks", type: "integer", example: 4),
                                new OA\Property(property: "remaining_tasks", type: "integer", example: 2),
                                new OA\Property(property: "earned_points", type: "integer", example: 60),
                                new OA\Property(property: "completion_percentage", type: "number", format: "float", example: 66.67),
                                new OA\Property(
                                    property: "daily_checkin",
                                    nullable: true,
                                    properties: [
                                        new OA\Property(property: "id", type: "string", format: "uuid", example: "9b3d9d30-b9bd-474c-8822-67cc3b922b06"),
                                        new OA\Property(property: "checkin_date", type: "string", format: "date", example: "2026-07-09"),
                                        new OA\Property(property: "streak_count", type: "integer", example: 7)
                                    ]
                                )
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Unauthenticated", content: new OA\JsonContent(ref: "#/components/schemas/UnauthorizedResponse"))
        ]
    )]
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
