<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\StreakResource;
use App\Services\StreakService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StreakController extends Controller
{
    public function __construct(private readonly StreakService $streakService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => 'Streak statistics retrieved successfully',
            'data' => new StreakResource($this->streakService->getStatistics($request->user())),
        ]);
    }

    public function current(Request $request): JsonResponse
    {
        $streak = $this->streakService->getStatistics($request->user());

        return response()->json([
            'status' => true,
            'message' => 'Current streak retrieved successfully',
            'data' => [
                'current_streak' => $streak->current_streak,
            ],
        ]);
    }
}
