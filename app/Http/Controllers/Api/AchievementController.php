<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AchievementResource;
use App\Http\Resources\UserAchievementResource;
use App\Services\AchievementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AchievementController extends Controller
{
    public function __construct(private readonly AchievementService $achievementService)
    {
    }

    public function index(): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => 'Achievements retrieved successfully',
            'data' => AchievementResource::collection($this->achievementService->availableAchievements()),
        ]);
    }

    public function myAchievements(Request $request): JsonResponse
    {
        $achievements = $this->achievementService->userAchievements(
            $request->user(),
            (int) $request->integer('per_page', 15)
        );

        return response()->json([
            'status' => true,
            'message' => 'User achievements retrieved successfully',
            'data' => UserAchievementResource::collection($achievements->items()),
            'meta' => [
                'current_page' => $achievements->currentPage(),
                'last_page' => $achievements->lastPage(),
                'per_page' => $achievements->perPage(),
                'total' => $achievements->total(),
            ],
        ]);
    }
}
