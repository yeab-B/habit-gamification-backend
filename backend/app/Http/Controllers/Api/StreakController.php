<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\StreakResource;
use App\Services\StreakService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class StreakController extends Controller
{
    public function __construct(private readonly StreakService $streakService)
    {
    }

    #[OA\Get(
        path: "/streaks",
        summary: "Get streak statistics",
        description: "Retrieves complete streak history and statistics for the user.",
        security: [["bearerAuth" => []]],
        tags: ["Streaks"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Streak statistics retrieved successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Streak statistics retrieved successfully"),
                        new OA\Property(
                            property: "data",
                            properties: [
                                new OA\Property(property: "id", type: "string", format: "uuid", example: "9b3d9d30-b9bd-474c-8822-67cc3b922b07"),
                                new OA\Property(property: "current_streak", type: "integer", example: 7),
                                new OA\Property(property: "longest_streak", type: "integer", example: 21),
                                new OA\Property(property: "last_activity_date", type: "string", format: "date", example: "2026-07-09")
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Unauthenticated", content: new OA\JsonContent(ref: "#/components/schemas/UnauthorizedResponse"))
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => 'Streak statistics retrieved successfully',
            'data' => new StreakResource($this->streakService->getStatistics($request->user())),
        ]);
    }

    #[OA\Get(
        path: "/streaks/current",
        summary: "Get current streak value",
        description: "Retrieves only the current streak number for quick display.",
        security: [["bearerAuth" => []]],
        tags: ["Streaks"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Current streak retrieved successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Current streak retrieved successfully"),
                        new OA\Property(
                            property: "data",
                            properties: [
                                new OA\Property(property: "current_streak", type: "integer", example: 7)
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Unauthenticated", content: new OA\JsonContent(ref: "#/components/schemas/UnauthorizedResponse"))
        ]
    )]
    public function current(Request $request): JsonResponse
    {
        $streak = $this->streakService->getStatistics($request->user());

        return response()->json([
            'status' => true,
            'message' => 'Current streak retrieved successfully',
            'data' => [
                'current_streak' => $streak->current_streak,
            ],
        ]);
    }
}
