<?php

namespace App\Finance\Controllers;

use App\Finance\Models\Expense;
use App\Finance\Requests\StoreExpenseRequest;
use App\Finance\Resources\ExpenseResource;
use App\Finance\Services\ExpenseService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function __construct(private readonly ExpenseService $expenseService)
    {
    }

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

    public function store(StoreExpenseRequest $request): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => 'Expense created successfully',
            'data' => new ExpenseResource($this->expenseService->createExpense($request->user(), $request->validated())),
        ], 201);
    }

    public function update(StoreExpenseRequest $request, Expense $expense): JsonResponse
    {
        abort_unless($request->user()->can('update', $expense), 403);

        return response()->json([
            'status' => true,
            'message' => 'Expense updated successfully',
            'data' => new ExpenseResource($this->expenseService->updateExpense($expense, $request->validated())),
        ]);
    }

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
