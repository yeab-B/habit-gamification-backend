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

class DashboardController extends Controller
{
    public function __construct(
        private readonly DashboardService $dashboardService,
        private readonly StatisticsService $statisticsService
    ) {
    }

    public function dashboard(Request $request): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => 'Dashboard retrieved successfully',
            'data' => new DashboardResource($this->dashboardService->dashboard($request->user())),
        ]);
    }

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
