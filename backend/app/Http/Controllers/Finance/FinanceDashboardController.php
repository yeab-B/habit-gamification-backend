<?php

namespace App\Http\Controllers\Finance;

use App\Http\Resources\Finance\FinanceDashboardResource;
use App\Http\Resources\Finance\FinanceStatisticsResource;
use App\Services\Finance\FinanceDashboardService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FinanceDashboardController extends Controller
{
    public function __construct(private readonly FinanceDashboardService $financeDashboardService)
    {
    }

    public function dashboard(Request $request): JsonResponse
    {
        $data = $this->financeDashboardService->dashboard($request->user());

        return response()->json([
            'status' => true,
            'message' => 'Finance dashboard retrieved successfully',
            'data' => new FinanceDashboardResource($data),
        ]);
    }

    public function statistics(Request $request): JsonResponse
    {
        $data = $request->validate(['period' => ['sometimes', 'string', 'in:weekly,monthly,yearly']]);

        $statistics = $this->financeDashboardService->statistics(
            $request->user(),
            $data['period'] ?? 'monthly',
        );

        return response()->json([
            'status' => true,
            'message' => 'Finance statistics retrieved successfully',
            'data' => new FinanceStatisticsResource($statistics),
        ]);
    }
}
