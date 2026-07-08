<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Challenge\StoreChallengeRequest;
use App\Http\Requests\Challenge\UpdateChallengeRequest;
use App\Http\Resources\ChallengeResource;
use App\Models\Challenge;
use App\Services\ChallengeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class ChallengeController extends Controller
{
    public function __construct(private readonly ChallengeService $challengeService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $challenges = $this->challengeService->getChallenges($request->user());

        return response()->json([
            'status' => true,
            'message' => 'Challenges retrieved successfully',
            'data' => ChallengeResource::collection($challenges),
        ]);
    }

    public function show(Challenge $challenge): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => 'Challenge details retrieved successfully',
            'data' => new ChallengeResource($this->challengeService->getChallenge($challenge)),
        ]);
    }

    public function store(StoreChallengeRequest $request): JsonResponse
    {
        $challenge = $this->challengeService->createChallenge($request->user(), $request->validated());

        return response()->json([
            'status' => true,
            'message' => 'Challenge created successfully',
            'data' => new ChallengeResource($challenge),
        ], 201);
    }

    public function update(UpdateChallengeRequest $request, Challenge $challenge): JsonResponse
    {
        $challenge = $this->challengeService->updateChallenge($challenge, $request->validated());

        return response()->json([
            'status' => true,
            'message' => 'Challenge updated successfully',
            'data' => new ChallengeResource($challenge),
        ]);
    }

    public function destroy(Request $request, Challenge $challenge): JsonResponse
    {
        if (! $request->user()->can('delete', $challenge)) {
            return response()->json([
                'status' => false,
                'message' => 'This action is unauthorized.',
                'errors' => [
                    'authorization' => ['You are not allowed to perform this action.'],
                ],
            ], 403);
        }

        $this->challengeService->deleteChallenge($challenge);

        return response()->json([
            'status' => true,
            'message' => 'Challenge deleted successfully',
            'data' => [],
        ]);
    }

    public function join(Request $request, Challenge $challenge): JsonResponse
    {
        if (! $request->user()->can('join', $challenge)) {
            return response()->json([
                'status' => false,
                'message' => 'This action is unauthorized.',
                'errors' => [
                    'authorization' => ['You are not allowed to perform this action.'],
                ],
            ], 403);
        }

        try {
            $this->challengeService->joinChallenge($challenge, $request->user());
        } catch (RuntimeException $exception) {
            return response()->json([
                'status' => false,
                'message' => $exception->getMessage(),
            ], 422);
        }

        return response()->json([
            'status' => true,
            'message' => 'Joined challenge successfully',
        ]);
    }

    public function leave(Request $request, Challenge $challenge): JsonResponse
    {
        if (! $request->user()->can('leave', $challenge)) {
            return response()->json([
                'status' => false,
                'message' => 'This action is unauthorized.',
                'errors' => [
                    'authorization' => ['You are not allowed to perform this action.'],
                ],
            ], 403);
        }

        try {
            $this->challengeService->leaveChallenge($challenge, $request->user());
        } catch (RuntimeException $exception) {
            return response()->json([
                'status' => false,
                'message' => $exception->getMessage(),
            ], 422);
        }

        return response()->json([
            'status' => true,
            'message' => 'Left challenge successfully',
        ]);
    }
}
