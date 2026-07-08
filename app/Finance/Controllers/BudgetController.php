<?php

namespace App\Finance\Controllers;

use App\Finance\Requests\UpdateBudgetSettingsRequest;
use App\Finance\Resources\BudgetResource;
use App\Finance\Services\BudgetService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BudgetController extends Controller
{
    public function __construct(private readonly BudgetService $budgetService)
    {
    }

    public function show(Request $request): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => 'Budget retrieved successfully',
            'data' => new BudgetResource($this->budgetService->getBudget($request->user())),
        ]);
    }

    public function settings(UpdateBudgetSettingsRequest $request): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => 'Budget settings updated successfully',
            'data' => $this->budgetService->updateSettings($request->user(), $request->validated()),
        ]);
    }
}
