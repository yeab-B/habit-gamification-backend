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
use OpenApi\Attributes as OA;

class TaskController extends Controller
{
    public function __construct(private readonly TaskService $taskService)
    {
    }

    #[OA\Get(
        path: "/tasks",
        summary: "List tasks",
        description: "Retrieves a list of tasks for the authenticated user, optionally filtered by category.",
        security: [["bearerAuth" => []]],
        tags: ["Tasks"],
        parameters: [
            new OA\Parameter(
                name: "category_id",
                in: "query",
                required: false,
                description: "Filter tasks by Category UUID",
                schema: new OA\Schema(type: "string", format: "uuid")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Tasks retrieved successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Tasks retrieved successfully"),
                        new OA\Property(
                            property: "data",
                            type: "array",
                            items: new OA\Items(ref: "#/components/schemas/Task")
                        )
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Unauthenticated", content: new OA\JsonContent(ref: "#/components/schemas/UnauthorizedResponse"))
        ]
    )]
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

    #[OA\Post(
        path: "/tasks",
        summary: "Create a task",
        description: "Creates a new task for the authenticated user in a category.",
        security: [["bearerAuth" => []]],
        tags: ["Tasks"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: "#/components/schemas/StoreTaskRequest")
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Task created successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Task created successfully"),
                        new OA\Property(property: "data", ref: "#/components/schemas/Task")
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Unauthenticated", content: new OA\JsonContent(ref: "#/components/schemas/UnauthorizedResponse")),
            new OA\Response(response: 422, description: "Validation failure", content: new OA\JsonContent(ref: "#/components/schemas/ValidationErrorResponse"))
        ]
    )]
    public function store(StoreTaskRequest $request): JsonResponse
    {
        $task = $this->taskService->createTask($request->user(), $request->validated());

        return response()->json([
            'status' => true,
            'message' => 'Task created successfully',
            'data' => new TaskResource($task),
        ], 201);
    }

    #[OA\Put(
        path: "/tasks/{task}",
        summary: "Update a task",
        description: "Updates an existing task's details.",
        security: [["bearerAuth" => []]],
        tags: ["Tasks"],
        parameters: [
            new OA\Parameter(
                name: "task",
                in: "path",
                required: true,
                description: "The ID of the task",
                schema: new OA\Schema(type: "string", format: "uuid")
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: "#/components/schemas/StoreTaskRequest")
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Task updated successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Task updated successfully"),
                        new OA\Property(property: "data", ref: "#/components/schemas/Task")
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Unauthenticated", content: new OA\JsonContent(ref: "#/components/schemas/UnauthorizedResponse")),
            new OA\Response(response: 403, description: "Unauthorized access", content: new OA\JsonContent(ref: "#/components/schemas/ForbiddenResponse")),
            new OA\Response(response: 404, description: "Task not found", content: new OA\JsonContent(ref: "#/components/schemas/NotFoundResponse")),
            new OA\Response(response: 422, description: "Validation failure", content: new OA\JsonContent(ref: "#/components/schemas/ValidationErrorResponse"))
        ]
    )]
    public function update(UpdateTaskRequest $request, Task $task): JsonResponse
    {
        $updatedTask = $this->taskService->updateTask($task, $request->validated());

        return response()->json([
            'status' => true,
            'message' => 'Task updated successfully',
            'data' => new TaskResource($updatedTask),
        ]);
    }

    #[OA\Delete(
        path: "/tasks/{task}",
        summary: "Delete a task",
        description: "Deletes a task from the user's list.",
        security: [["bearerAuth" => []]],
        tags: ["Tasks"],
        parameters: [
            new OA\Parameter(
                name: "task",
                in: "path",
                required: true,
                description: "The ID of the task",
                schema: new OA\Schema(type: "string", format: "uuid")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Task deleted successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Task deleted successfully"),
                        new OA\Property(property: "data", type: "array", items: new OA\Items(type: "string"), example: [])
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Unauthenticated", content: new OA\JsonContent(ref: "#/components/schemas/UnauthorizedResponse")),
            new OA\Response(response: 403, description: "Unauthorized access", content: new OA\JsonContent(ref: "#/components/schemas/ForbiddenResponse")),
            new OA\Response(response: 404, description: "Task not found", content: new OA\JsonContent(ref: "#/components/schemas/NotFoundResponse"))
        ]
    )]
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
