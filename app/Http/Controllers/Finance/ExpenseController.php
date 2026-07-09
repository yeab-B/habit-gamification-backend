<?php

namespace App\Http\Controllers\Finance;

use App\Models\Finance\Expense;
use App\Http\Requests\Finance\StoreExpenseRequest;
use App\Http\Resources\Finance\ExpenseResource;
use App\Services\Finance\ExpenseService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class ExpenseController extends Controller
{
    public function __construct(private readonly ExpenseService $expenseService)
    {
    }

    #[OA\Get(
        path: "/finance/expenses",
        summary: "List expenses",
        description: "Retrieves a paginated list of expenses for the user and total spending in ETB.",
        security: [["bearerAuth" => []]],
        tags: ["Finance"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Expenses retrieved successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Expenses retrieved successfully"),
                        new OA\Property(property: "data", type: "array", items: new OA\Items(ref: "#/components/schemas/Expense")),
                        new OA\Property(
                            property: "spending",
                            properties: [
                                new OA\Property(property: "total_spending", type: "number", format: "float", example: 45000.00),
                                new OA\Property(property: "currency", type: "string", example: "ETB")
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
        $expenses = $this->expenseService->getExpenses($request->user());

        return response()->json([
            'status' => true,
            'message' => 'Expenses retrieved successfully',
            'data' => ExpenseResource::collection($expenses->items()),
            'spending' => $this->expenseService->calculateSpending($request->user()),
        ]);
    }

    #[OA\Post(
        path: "/finance/expenses",
        summary: "Create expense record",
        description: "Creates a new expense record in ETB.",
        security: [["bearerAuth" => []]],
        tags: ["Finance"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: "#/components/schemas/StoreExpenseRequest")
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Expense created successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Expense created successfully"),
                        new OA\Property(property: "data", ref: "#/components/schemas/Expense")
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Unauthenticated", content: new OA\JsonContent(ref: "#/components/schemas/UnauthorizedResponse")),
            new OA\Response(response: 422, description: "Validation failure", content: new OA\JsonContent(ref: "#/components/schemas/ValidationErrorResponse"))
        ]
    )]
    public function store(StoreExpenseRequest $request): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => 'Expense created successfully',
            'data' => new ExpenseResource($this->expenseService->createExpense($request->user(), $request->validated())),
        ], 201);
    }

    #[OA\Put(
        path: "/finance/expenses/{expense}",
        summary: "Update expense record",
        description: "Updates an existing expense record.",
        security: [["bearerAuth" => []]],
        tags: ["Finance"],
        parameters: [
            new OA\Parameter(name: "expense", in: "path", required: true, description: "The ID of the expense", schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: "#/components/schemas/StoreExpenseRequest")
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Expense updated successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Expense updated successfully"),
                        new OA\Property(property: "data", ref: "#/components/schemas/Expense")
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Unauthenticated", content: new OA\JsonContent(ref: "#/components/schemas/UnauthorizedResponse")),
            new OA\Response(response: 403, description: "Unauthorized access", content: new OA\JsonContent(ref: "#/components/schemas/ForbiddenResponse")),
            new OA\Response(response: 404, description: "Expense not found", content: new OA\JsonContent(ref: "#/components/schemas/NotFoundResponse")),
            new OA\Response(response: 422, description: "Validation failure", content: new OA\JsonContent(ref: "#/components/schemas/ValidationErrorResponse"))
        ]
    )]
    public function update(StoreExpenseRequest $request, Expense $expense): JsonResponse
    {
        abort_unless($request->user()->can('update', $expense), 403);

        return response()->json([
            'status' => true,
            'message' => 'Expense updated successfully',
            'data' => new ExpenseResource($this->expenseService->updateExpense($expense, $request->validated())),
        ]);
    }

    #[OA\Delete(
        path: "/finance/expenses/{expense}",
        summary: "Delete expense record",
        description: "Deletes an expense record.",
        security: [["bearerAuth" => []]],
        tags: ["Finance"],
        parameters: [
            new OA\Parameter(name: "expense", in: "path", required: true, description: "The ID of the expense", schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Expense deleted successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Expense deleted successfully"),
                        new OA\Property(property: "data", type: "array", items: new OA\Items(type: "string"), example: [])
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Unauthenticated", content: new OA\JsonContent(ref: "#/components/schemas/UnauthorizedResponse")),
            new OA\Response(response: 403, description: "Unauthorized access", content: new OA\JsonContent(ref: "#/components/schemas/ForbiddenResponse")),
            new OA\Response(response: 404, description: "Expense not found", content: new OA\JsonContent(ref: "#/components/schemas/NotFoundResponse"))
        ]
    )]
    public function destroy(Request $request, Expense $expense): JsonResponse
    {
        abort_unless($request->user()->can('delete', $expense), 403);

        $this->expenseService->deleteExpense($expense);

        return response()->json([
            'status' => true,
            'message' => 'Expense deleted successfully',
            'data' => [],
        ]);
    }
}
