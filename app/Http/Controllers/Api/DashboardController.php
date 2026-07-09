<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\DashboardResource;
use App\Http\Resources\StatisticsResource;
use App\Services\DashboardService;
use App\Services\StatisticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use OpenApi\Attributes as OA;

class DashboardController extends Controller
{
    public function __construct(
        private readonly DashboardService $dashboardService,
        private readonly StatisticsService $statisticsService
    ) {
    }

    #[OA\Get(
        path: "/dashboard",
        summary: "Get dashboard overview",
        description: "Retrieves main dashboard statistics (streaks, coins, task completions).",
        security: [["bearerAuth" => []]],
        tags: ["Dashboard"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Dashboard retrieved successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Dashboard retrieved successfully"),
                        new OA\Property(property: "data", ref: "#/components/schemas/Dashboard")
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Unauthenticated", content: new OA\JsonContent(ref: "#/components/schemas/UnauthorizedResponse"))
        ]
    )]
    public function dashboard(Request $request): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => 'Dashboard retrieved successfully',
            'data' => new DashboardResource($this->dashboardService->dashboard($request->user())),
        ]);
    }

    #[OA\Get(
        path: "/statistics",
        summary: "Get task completion statistics",
        description: "Retrieves task completion reports grouped by week, month, or year.",
        security: [["bearerAuth" => []]],
        tags: ["Statistics"],
        parameters: [
            new OA\Parameter(
                name: "period",
                in: "query",
                required: false,
                description: "Grouping period for stats",
                schema: new OA\Schema(type: "string", enum: ["weekly", "monthly", "yearly"], default: "monthly")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Statistics retrieved successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Statistics retrieved successfully"),
                        new OA\Property(
                            property: "data",
                            properties: [
                                new OA\Property(property: "period", type: "string", example: "monthly"),
                                new OA\Property(
                                    property: "completion_rates",
                                    type: "array",
                                    items: new OA\Items(
                                        properties: [
                                            new OA\Property(property: "label", type: "string", example: "June 2026"),
                                            new OA\Property(property: "rate", type: "number", format: "float", example: 78.5)
                                        ]
                                    )
                                )
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Unauthenticated", content: new OA\JsonContent(ref: "#/components/schemas/UnauthorizedResponse")),
            new OA\Response(response: 422, description: "Validation failure", content: new OA\JsonContent(ref: "#/components/schemas/ValidationErrorResponse"))
        ]
    )]
    public function statistics(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'period' => ['sometimes', 'string', Rule::in(['weekly', 'monthly', 'yearly'])],
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Statistics retrieved successfully',
            'data' => new StatisticsResource(
                $this->statisticsService->report($request->user(), $validated['period'] ?? 'monthly')
            ),
        ]);
    }
}
