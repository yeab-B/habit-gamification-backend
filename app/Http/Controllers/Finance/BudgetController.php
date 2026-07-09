<?php

namespace App\Http\Controllers\Finance;

use App\Http\Requests\Finance\UpdateBudgetSettingsRequest;
use App\Http\Resources\Finance\BudgetResource;
use App\Services\Finance\BudgetService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class BudgetController extends Controller
{
    public function __construct(private readonly BudgetService $budgetService)
    {
    }

    #[OA\Get(
        path: "/finance/budget",
        summary: "Get current budget settings",
        description: "Retrieves the current budget allocation percentages for Asrat, Needs, Emergency, Investment, and Rewards.",
        security: [["bearerAuth" => []]],
        tags: ["Finance"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Budget retrieved successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Budget retrieved successfully"),
                        new OA\Property(property: "data", ref: "#/components/schemas/BudgetAllocation")
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Unauthenticated", content: new OA\JsonContent(ref: "#/components/schemas/UnauthorizedResponse"))
        ]
    )]
    public function show(Request $request): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => 'Budget retrieved successfully',
            'data' => new BudgetResource($this->budgetService->getBudget($request->user())),
        ]);
    }

    #[OA\Post(
        path: "/finance/budget/settings",
        summary: "Update budget settings",
        description: "Updates the percentage allocations of income towards budget categories. Total must sum up to 100%.",
        security: [["bearerAuth" => []]],
        tags: ["Finance"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: "#/components/schemas/UpdateBudgetSettingsRequest")
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Budget settings updated successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Budget settings updated successfully"),
                        new OA\Property(property: "data", ref: "#/components/schemas/BudgetAllocation")
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Unauthenticated", content: new OA\JsonContent(ref: "#/components/schemas/UnauthorizedResponse")),
            new OA\Response(response: 422, description: "Validation failure", content: new OA\JsonContent(ref: "#/components/schemas/ValidationErrorResponse"))
        ]
    )]
    public function settings(UpdateBudgetSettingsRequest $request): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => 'Budget settings updated successfully',
            'data' => $this->budgetService->updateSettings($request->user(), $request->validated()),
        ]);
    }
}
