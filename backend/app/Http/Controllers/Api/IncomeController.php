<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Finance\StoreIncomeRequest;
use App\Http\Requests\Finance\UpdateIncomeRequest;
use App\Http\Resources\IncomeResource;
use App\Models\Income;
use App\Services\IncomeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;
use RuntimeException;

class IncomeController extends Controller
{
    public function __construct(private readonly IncomeService $incomeService)
    {
    }

    #[OA\Get(
        path: "/incomes",
        summary: "List income history",
        description: "Retrieves a paginated list of incomes, with optional filters by source and date range, plus summary metrics in ETB.",
        security: [["bearerAuth" => []]],
        tags: ["Finance"],
        parameters: [
            new OA\Parameter(name: "source_id", in: "query", required: false, description: "Income source UUID filter", schema: new OA\Schema(type: "string", format: "uuid")),
            new OA\Parameter(name: "start_date", in: "query", required: false, description: "Filter start date (YYYY-MM-DD)", schema: new OA\Schema(type: "string", format: "date")),
            new OA\Parameter(name: "end_date", in: "query", required: false, description: "Filter end date (YYYY-MM-DD)", schema: new OA\Schema(type: "string", format: "date")),
            new OA\Parameter(name: "page", in: "query", required: false, description: "Page number", schema: new OA\Schema(type: "integer", minimum: 1))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Income history retrieved successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Income history retrieved successfully"),
                        new OA\Property(property: "data", type: "array", items: new OA\Items(ref: "#/components/schemas/Income")),
                        new OA\Property(
                            property: "summary",
                            properties: [
                                new OA\Property(property: "total_income", type: "number", format: "float", example: 85000.00),
                                new OA\Property(property: "currency", type: "string", example: "ETB")
                            ]
                        ),
                        new OA\Property(
                            property: "by_source",
                            type: "array",
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: "name", type: "string", example: "Salary"),
                                    new OA\Property(property: "total", type: "number", format: "float", example: 60000.00)
                                ]
                            )
                        ),
                        new OA\Property(
                            property: "meta",
                            properties: [
                                new OA\Property(property: "current_page", type: "integer", example: 1),
                                new OA\Property(property: "last_page", type: "integer", example: 2),
                                new OA\Property(property: "per_page", type: "integer", example: 15),
                                new OA\Property(property: "total", type: "integer", example: 20)
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Unauthenticated", content: new OA\JsonContent(ref: "#/components/schemas/UnauthorizedResponse")),
            new OA\Response(response: 422, description: "Validation failure", content: new OA\JsonContent(ref: "#/components/schemas/ValidationErrorResponse"))
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'source_id' => ['sometimes', 'uuid'],
            'start_date' => ['sometimes', 'date'],
            'end_date' => ['sometimes', 'date'],
            'page' => ['sometimes', 'integer', 'min:1'],
        ]);

        $incomes = $this->incomeService->getIncomeHistory($request->user(), $filters);

        return response()->json([
            'status' => true,
            'message' => 'Income history retrieved successfully',
            'data' => IncomeResource::collection($incomes->items()),
            'summary' => $this->incomeService->getSummary($request->user()),
            'by_source' => $this->incomeService->getIncomeBySource($request->user()),
            'meta' => [
                'current_page' => $incomes->currentPage(),
                'last_page' => $incomes->lastPage(),
                'per_page' => $incomes->perPage(),
                'total' => $incomes->total(),
            ],
        ]);
    }

    #[OA\Post(
        path: "/incomes",
        summary: "Create income record",
        description: "Records a new income, calculates Asrat (10% tithe by default), and allocates remaining amount to budget targets.",
        security: [["bearerAuth" => []]],
        tags: ["Finance"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: "#/components/schemas/StoreIncomeRequest")
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Income created successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Income created successfully"),
                        new OA\Property(property: "data", ref: "#/components/schemas/Income")
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Unauthenticated", content: new OA\JsonContent(ref: "#/components/schemas/UnauthorizedResponse")),
            new OA\Response(response: 422, description: "Validation/Business rule failure", content: new OA\JsonContent(ref: "#/components/schemas/ValidationErrorResponse"))
        ]
    )]
    public function store(StoreIncomeRequest $request): JsonResponse
    {
        try {
            $income = $this->incomeService->createIncome($request->user(), $request->validated());
        } catch (RuntimeException $exception) {
            return $this->businessRuleError($exception);
        }

        $this->attachAsrat($income);

        return response()->json([
            'status' => true,
            'message' => 'Income created successfully',
            'data' => new IncomeResource($income),
        ], 201);
    }

    #[OA\Put(
        path: "/incomes/{income}",
        summary: "Update income record",
        description: "Updates an existing income record.",
        security: [["bearerAuth" => []]],
        tags: ["Finance"],
        parameters: [
            new OA\Parameter(name: "income", in: "path", required: true, description: "The ID of the income record", schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: "#/components/schemas/StoreIncomeRequest")
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Income updated successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Income updated successfully"),
                        new OA\Property(property: "data", ref: "#/components/schemas/Income")
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Unauthenticated", content: new OA\JsonContent(ref: "#/components/schemas/UnauthorizedResponse")),
            new OA\Response(response: 403, description: "Unauthorized access", content: new OA\JsonContent(ref: "#/components/schemas/ForbiddenResponse")),
            new OA\Response(response: 404, description: "Income not found", content: new OA\JsonContent(ref: "#/components/schemas/NotFoundResponse")),
            new OA\Response(response: 422, description: "Validation failure", content: new OA\JsonContent(ref: "#/components/schemas/ValidationErrorResponse"))
        ]
    )]
    public function update(UpdateIncomeRequest $request, Income $income): JsonResponse
    {
        try {
            $income = $this->incomeService->updateIncome($income, $request->validated());
        } catch (RuntimeException $exception) {
            return $this->businessRuleError($exception);
        }

        $this->attachAsrat($income);

        return response()->json([
            'status' => true,
            'message' => 'Income updated successfully',
            'data' => new IncomeResource($income),
        ]);
    }

    #[OA\Delete(
        path: "/incomes/{income}",
        summary: "Delete income record",
        description: "Deletes an income record and reverts allocations.",
        security: [["bearerAuth" => []]],
        tags: ["Finance"],
        parameters: [
            new OA\Parameter(name: "income", in: "path", required: true, description: "The ID of the income record", schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Income deleted successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Income deleted successfully"),
                        new OA\Property(property: "data", type: "array", items: new OA\Items(type: "string"), example: [])
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Unauthenticated", content: new OA\JsonContent(ref: "#/components/schemas/UnauthorizedResponse")),
            new OA\Response(response: 403, description: "Unauthorized access", content: new OA\JsonContent(ref: "#/components/schemas/ForbiddenResponse")),
            new OA\Response(response: 404, description: "Income not found", content: new OA\JsonContent(ref: "#/components/schemas/NotFoundResponse"))
        ]
    )]
    public function destroy(Request $request, Income $income): JsonResponse
    {
        if (! $request->user()->can('delete', $income)) {
            return response()->json([
                'status' => false,
                'message' => 'This action is unauthorized.',
                'errors' => [
                    'authorization' => ['You are not allowed to perform this action.'],
                ],
            ], 403);
        }

        $this->incomeService->deleteIncome($income);

        return response()->json([
            'status' => true,
            'message' => 'Income deleted successfully',
            'data' => [],
        ]);
    }

    private function attachAsrat(Income $income): void
    {
        $calculation = $this->incomeService->asratFor($income);

        $income->setAttribute('asrat', $calculation['asrat']);
        $income->setAttribute('remaining_after_asrat', $calculation['remaining']);
    }

    private function businessRuleError(RuntimeException $exception): JsonResponse
    {
        return response()->json([
            'status' => false,
            'message' => $exception->getMessage(),
        ], 422);
    }
}
