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
use OpenApi\Attributes as OA;
use RuntimeException;

class FriendController extends Controller
{
    public function __construct(private readonly FriendService $friendService)
    {
    }

    #[OA\Get(
        path: "/users/search",
        summary: "Search for users",
        description: "Searches users by name or email to send friend requests.",
        security: [["bearerAuth" => []]],
        tags: ["Friends", "Search"],
        parameters: [
            new OA\Parameter(
                name: "search",
                in: "query",
                required: false,
                description: "Search keyword (name or email)",
                schema: new OA\Schema(type: "string")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Users retrieved successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Users retrieved successfully"),
                        new OA\Property(
                            property: "data",
                            type: "array",
                            items: new OA\Items(ref: "#/components/schemas/User")
                        )
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Unauthenticated", content: new OA\JsonContent(ref: "#/components/schemas/UnauthorizedResponse"))
        ]
    )]
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

    #[OA\Get(
        path: "/friends",
        summary: "List friends",
        description: "Retrieves a list of all accepted friendships with friend profiles.",
        security: [["bearerAuth" => []]],
        tags: ["Friends"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Friends retrieved successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Friends retrieved successfully"),
                        new OA\Property(
                            property: "data",
                            type: "array",
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(
                                        property: "friendship",
                                        properties: [
                                            new OA\Property(property: "id", type: "string", format: "uuid", example: "9b3d9d30-b9bd-474c-8822-67cc3b922b09"),
                                            new OA\Property(property: "status", type: "string", example: "accepted")
                                        ]
                                    ),
                                    new OA\Property(property: "friend", ref: "#/components/schemas/User")
                                ]
                            )
                        )
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Unauthenticated", content: new OA\JsonContent(ref: "#/components/schemas/UnauthorizedResponse"))
        ]
    )]
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

    #[OA\Post(
        path: "/friends/request",
        summary: "Send friend request",
        description: "Sends a new friendship request to another user.",
        security: [["bearerAuth" => []]],
        tags: ["Friends"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["user_id"],
                properties: [
                    new OA\Property(property: "user_id", type: "string", format: "uuid", example: "9b3d9d30-b3bd-474c-8822-67cc3b922a94")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Friend request sent successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Friend request sent successfully"),
                        new OA\Property(
                            property: "data",
                            properties: [
                                new OA\Property(property: "id", type: "string", format: "uuid", example: "9b3d9d30-b9bd-474c-8822-67cc3b922b09"),
                                new OA\Property(property: "sender_id", type: "string", format: "uuid", example: "9b3d9d30-a3bd-474c-8822-67cc3b922a93"),
                                new OA\Property(property: "receiver_id", type: "string", format: "uuid", example: "9b3d9d30-b3bd-474c-8822-67cc3b922a94"),
                                new OA\Property(property: "status", type: "string", example: "pending"),
                                new OA\Property(property: "created_at", type: "string", format: "date-time", example: "2026-07-09T03:56:49.000000Z")
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Unauthenticated", content: new OA\JsonContent(ref: "#/components/schemas/UnauthorizedResponse")),
            new OA\Response(response: 422, description: "Validation/Business rule failure", content: new OA\JsonContent(ref: "#/components/schemas/ValidationErrorResponse"))
        ]
    )]
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

    #[OA\Post(
        path: "/friends/accept",
        summary: "Accept friend request",
        description: "Accepts a pending friend request.",
        security: [["bearerAuth" => []]],
        tags: ["Friends"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["id"],
                properties: [
                    new OA\Property(property: "id", type: "string", format: "uuid", example: "9b3d9d30-b9bd-474c-8822-67cc3b922b09")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Friend request accepted successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Friend request accepted successfully"),
                        new OA\Property(
                            property: "data",
                            properties: [
                                new OA\Property(property: "id", type: "string", format: "uuid", example: "9b3d9d30-b9bd-474c-8822-67cc3b922b09"),
                                new OA\Property(property: "status", type: "string", example: "accepted")
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Unauthenticated", content: new OA\JsonContent(ref: "#/components/schemas/UnauthorizedResponse")),
            new OA\Response(response: 422, description: "Validation failure", content: new OA\JsonContent(ref: "#/components/schemas/ValidationErrorResponse"))
        ]
    )]
    public function accept(AcceptFriendRequest $request): JsonResponse
    {
        $friendship = $this->friendService->accept($request->friendship());

        return response()->json([
            'status' => true,
            'message' => 'Friend request accepted successfully',
            'data' => new FriendshipResource($friendship->load(['sender', 'receiver'])),
        ]);
    }

    #[OA\Post(
        path: "/friends/reject",
        summary: "Reject friend request",
        description: "Rejects a pending friend request.",
        security: [["bearerAuth" => []]],
        tags: ["Friends"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["id"],
                properties: [
                    new OA\Property(property: "id", type: "string", format: "uuid", example: "9b3d9d30-b9bd-474c-8822-67cc3b922b09")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Friend request rejected successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Friend request rejected successfully"),
                        new OA\Property(
                            property: "data",
                            properties: [
                                new OA\Property(property: "id", type: "string", format: "uuid", example: "9b3d9d30-b9bd-474c-8822-67cc3b922b09"),
                                new OA\Property(property: "status", type: "string", example: "rejected")
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Unauthenticated", content: new OA\JsonContent(ref: "#/components/schemas/UnauthorizedResponse")),
            new OA\Response(response: 422, description: "Validation failure", content: new OA\JsonContent(ref: "#/components/schemas/ValidationErrorResponse"))
        ]
    )]
    public function reject(RejectFriendRequest $request): JsonResponse
    {
        $friendship = $this->friendService->reject($request->friendship());

        return response()->json([
            'status' => true,
            'message' => 'Friend request rejected successfully',
            'data' => new FriendshipResource($friendship->load(['sender', 'receiver'])),
        ]);
    }

    #[OA\Delete(
        path: "/friends/{id}",
        summary: "Remove friend",
        description: "Removes a friend and deletes the friendship.",
        security: [["bearerAuth" => []]],
        tags: ["Friends"],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                description: "The ID of the friendship",
                schema: new OA\Schema(type: "string", format: "uuid")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Friend removed successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Friend removed successfully"),
                        new OA\Property(property: "data", type: "array", items: new OA\Items(type: "string"), example: [])
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Unauthenticated", content: new OA\JsonContent(ref: "#/components/schemas/UnauthorizedResponse")),
            new OA\Response(response: 403, description: "Unauthorized access", content: new OA\JsonContent(ref: "#/components/schemas/ForbiddenResponse")),
            new OA\Response(response: 404, description: "Friendship not found", content: new OA\JsonContent(ref: "#/components/schemas/NotFoundResponse"))
        ]
    )]
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
