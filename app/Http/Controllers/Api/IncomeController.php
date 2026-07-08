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
use RuntimeException;

class IncomeController extends Controller
{
    public function __construct(private readonly IncomeService $incomeService)
    {
    }

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
