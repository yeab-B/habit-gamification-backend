<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Friend\AcceptFriendRequest;
use App\Http\Requests\Friend\RejectFriendRequest;
use App\Http\Requests\Friend\SendFriendRequest;
use App\Http\Resources\FriendResource;
use App\Http\Resources\FriendshipResource;
use App\Http\Resources\UserResource;
use App\Models\Friendship;
use App\Models\User;
use App\Services\FriendService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class FriendController extends Controller
{
    public function __construct(private readonly FriendService $friendService)
    {
    }

    public function search(Request $request): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => 'Users retrieved successfully',
            'data' => UserResource::collection(
                $this->friendService->searchUsers($request->user(), $request->query('search'))
            ),
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $friends = $this->friendService->getFriends($request->user())
            ->map(fn (Friendship $friendship) => [
                'friendship' => $friendship,
                'friend' => $this->friendService->friendFor($friendship, $request->user()),
            ]);

        return response()->json([
            'status' => true,
            'message' => 'Friends retrieved successfully',
            'data' => FriendResource::collection($friends),
        ]);
    }

    public function sendRequest(SendFriendRequest $request): JsonResponse
    {
        try {
            $friendship = $this->friendService->sendRequest(
                $request->user(),
                User::query()->findOrFail($request->validated('user_id'))
            );
        } catch (RuntimeException $exception) {
            return $this->businessRuleError($exception);
        }

        return response()->json([
            'status' => true,
            'message' => 'Friend request sent successfully',
            'data' => new FriendshipResource($friendship->load(['sender', 'receiver'])),
        ], 201);
    }

    public function accept(AcceptFriendRequest $request): JsonResponse
    {
        $friendship = $this->friendService->accept($request->friendship());

        return response()->json([
            'status' => true,
            'message' => 'Friend request accepted successfully',
            'data' => new FriendshipResource($friendship->load(['sender', 'receiver'])),
        ]);
    }

    public function reject(RejectFriendRequest $request): JsonResponse
    {
        $friendship = $this->friendService->reject($request->friendship());

        return response()->json([
            'status' => true,
            'message' => 'Friend request rejected successfully',
            'data' => new FriendshipResource($friendship->load(['sender', 'receiver'])),
        ]);
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        $friendship = Friendship::query()->findOrFail($id);

        if (! $request->user()->can('remove', $friendship)) {
            return response()->json([
                'status' => false,
                'message' => 'This action is unauthorized.',
                'errors' => [
                    'authorization' => ['You are not allowed to perform this action.'],
                ],
            ], 403);
        }

        $this->friendService->remove($friendship);

        return response()->json([
            'status' => true,
            'message' => 'Friend removed successfully',
            'data' => [],
        ]);
    }

    private function businessRuleError(RuntimeException $exception): JsonResponse
    {
        return response()->json([
            'status' => false,
            'message' => $exception->getMessage(),
        ], 422);
    }
}
