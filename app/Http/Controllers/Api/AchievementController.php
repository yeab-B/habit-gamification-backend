<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AchievementResource;
use App\Http\Resources\UserAchievementResource;
use App\Services\AchievementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class AchievementController extends Controller
{
    public function __construct(private readonly AchievementService $achievementService)
    {
    }

    #[OA\Get(
        path: "/achievements",
        summary: "List all achievements",
        description: "Retrieves a list of all defined achievements in the system.",
        security: [["bearerAuth" => []]],
        tags: ["Achievements"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Achievements retrieved successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Achievements retrieved successfully"),
                        new OA\Property(
                            property: "data",
                            type: "array",
                            items: new OA\Items(ref: "#/components/schemas/Achievement")
                        )
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Unauthenticated", content: new OA\JsonContent(ref: "#/components/schemas/UnauthorizedResponse"))
        ]
    )]
    public function index(): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => 'Achievements retrieved successfully',
            'data' => AchievementResource::collection($this->achievementService->availableAchievements()),
        ]);
    }

    #[OA\Get(
        path: "/my-achievements",
        summary: "Get earned achievements",
        description: "Retrieves the user's earned achievements with status and unlocked dates.",
        security: [["bearerAuth" => []]],
        tags: ["Achievements"],
        parameters: [
            new OA\Parameter(
                name: "per_page",
                in: "query",
                required: false,
                description: "Results per page",
                schema: new OA\Schema(type: "integer", minimum: 1, maximum: 100)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "User achievements retrieved successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "User achievements retrieved successfully"),
                        new OA\Property(
                            property: "data",
                            type: "array",
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: "id", type: "string", format: "uuid", example: "9b3d9d30-b9bd-474c-8822-67cc3b922b12"),
                                    new OA\Property(property: "unlocked_at", type: "string", format: "date-time", example: "2026-07-09T03:56:49.000000Z"),
                                    new OA\Property(property: "achievement", ref: "#/components/schemas/Achievement")
                                ]
                            )
                        ),
                        new OA\Property(
                            property: "meta",
                            properties: [
                                new OA\Property(property: "current_page", type: "integer", example: 1),
                                new OA\Property(property: "last_page", type: "integer", example: 1),
                                new OA\Property(property: "per_page", type: "integer", example: 15),
                                new OA\Property(property: "total", type: "integer", example: 3)
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Unauthenticated", content: new OA\JsonContent(ref: "#/components/schemas/UnauthorizedResponse"))
        ]
    )]
    public function myAchievements(Request $request): JsonResponse
    {
        $achievements = $this->achievementService->userAchievements(
            $request->user(),
            (int) $request->integer('per_page', 15)
        );

        return response()->json([
            'status' => true,
            'message' => 'User achievements retrieved successfully',
            'data' => UserAchievementResource::collection($achievements->items()),
            'meta' => [
                'current_page' => $achievements->currentPage(),
                'last_page' => $achievements->lastPage(),
                'per_page' => $achievements->perPage(),
                'total' => $achievements->total(),
            ],
        ]);
    }
}
