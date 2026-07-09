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
use OpenApi\Attributes as OA;
use RuntimeException;

class ChallengeController extends Controller
{
    public function __construct(private readonly ChallengeService $challengeService)
    {
    }

    #[OA\Get(
        path: "/challenges",
        summary: "List challenges",
        description: "Retrieves all available and joined challenges for the user.",
        security: [["bearerAuth" => []]],
        tags: ["Challenges"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Challenges retrieved successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Challenges retrieved successfully"),
                        new OA\Property(
                            property: "data",
                            type: "array",
                            items: new OA\Items(ref: "#/components/schemas/Challenge")
                        )
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Unauthenticated", content: new OA\JsonContent(ref: "#/components/schemas/UnauthorizedResponse"))
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $challenges = $this->challengeService->getChallenges();

        return response()->json([
            'status' => true,
            'message' => 'Challenges retrieved successfully',
            'data' => ChallengeResource::collection($challenges),
        ]);
    }

    #[OA\Get(
        path: "/challenges/{challenge}",
        summary: "Get challenge details",
        description: "Retrieves details of a specific challenge.",
        security: [["bearerAuth" => []]],
        tags: ["Challenges"],
        parameters: [
            new OA\Parameter(
                name: "challenge",
                in: "path",
                required: true,
                description: "The ID of the challenge",
                schema: new OA\Schema(type: "string", format: "uuid")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Challenge details retrieved successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Challenge details retrieved successfully"),
                        new OA\Property(property: "data", ref: "#/components/schemas/Challenge")
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Unauthenticated", content: new OA\JsonContent(ref: "#/components/schemas/UnauthorizedResponse")),
            new OA\Response(response: 404, description: "Challenge not found", content: new OA\JsonContent(ref: "#/components/schemas/NotFoundResponse"))
        ]
    )]
    public function show(Challenge $challenge): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => 'Challenge details retrieved successfully',
            'data' => new ChallengeResource($this->challengeService->getChallenge($challenge)),
        ]);
    }

    #[OA\Post(
        path: "/challenges",
        summary: "Create a challenge",
        description: "Creates a new challenge.",
        security: [["bearerAuth" => []]],
        tags: ["Challenges"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: "#/components/schemas/StoreChallengeRequest")
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Challenge created successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Challenge created successfully"),
                        new OA\Property(property: "data", ref: "#/components/schemas/Challenge")
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Unauthenticated", content: new OA\JsonContent(ref: "#/components/schemas/UnauthorizedResponse")),
            new OA\Response(response: 422, description: "Validation failure", content: new OA\JsonContent(ref: "#/components/schemas/ValidationErrorResponse"))
        ]
    )]
    public function store(StoreChallengeRequest $request): JsonResponse
    {
        $challenge = $this->challengeService->createChallenge($request->user(), $request->validated());

        return response()->json([
            'status' => true,
            'message' => 'Challenge created successfully',
            'data' => new ChallengeResource($challenge),
        ], 201);
    }

    #[OA\Put(
        path: "/challenges/{challenge}",
        summary: "Update challenge details",
        description: "Updates an existing challenge.",
        security: [["bearerAuth" => []]],
        tags: ["Challenges"],
        parameters: [
            new OA\Parameter(
                name: "challenge",
                in: "path",
                required: true,
                description: "The ID of the challenge",
                schema: new OA\Schema(type: "string", format: "uuid")
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: "#/components/schemas/StoreChallengeRequest")
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Challenge updated successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Challenge updated successfully"),
                        new OA\Property(property: "data", ref: "#/components/schemas/Challenge")
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Unauthenticated", content: new OA\JsonContent(ref: "#/components/schemas/UnauthorizedResponse")),
            new OA\Response(response: 403, description: "Unauthorized access", content: new OA\JsonContent(ref: "#/components/schemas/ForbiddenResponse")),
            new OA\Response(response: 404, description: "Challenge not found", content: new OA\JsonContent(ref: "#/components/schemas/NotFoundResponse")),
            new OA\Response(response: 422, description: "Validation failure", content: new OA\JsonContent(ref: "#/components/schemas/ValidationErrorResponse"))
        ]
    )]
    public function update(UpdateChallengeRequest $request, Challenge $challenge): JsonResponse
    {
        $challenge = $this->challengeService->updateChallenge($challenge, $request->validated());

        return response()->json([
            'status' => true,
            'message' => 'Challenge updated successfully',
            'data' => new ChallengeResource($challenge),
        ]);
    }

    #[OA\Delete(
        path: "/challenges/{challenge}",
        summary: "Delete a challenge",
        description: "Deletes a challenge.",
        security: [["bearerAuth" => []]],
        tags: ["Challenges"],
        parameters: [
            new OA\Parameter(
                name: "challenge",
                in: "path",
                required: true,
                description: "The ID of the challenge",
                schema: new OA\Schema(type: "string", format: "uuid")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Challenge deleted successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Challenge deleted successfully"),
                        new OA\Property(property: "data", type: "array", items: new OA\Items(type: "string"), example: [])
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Unauthenticated", content: new OA\JsonContent(ref: "#/components/schemas/UnauthorizedResponse")),
            new OA\Response(response: 403, description: "Unauthorized access", content: new OA\JsonContent(ref: "#/components/schemas/ForbiddenResponse")),
            new OA\Response(response: 404, description: "Challenge not found", content: new OA\JsonContent(ref: "#/components/schemas/NotFoundResponse"))
        ]
    )]
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

    #[OA\Post(
        path: "/challenges/{challenge}/join",
        summary: "Join a challenge",
        description: "Enrolls the authenticated user into a challenge.",
        security: [["bearerAuth" => []]],
        tags: ["Challenges"],
        parameters: [
            new OA\Parameter(
                name: "challenge",
                in: "path",
                required: true,
                description: "The ID of the challenge to join",
                schema: new OA\Schema(type: "string", format: "uuid")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Joined challenge successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Joined challenge successfully")
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Unauthenticated", content: new OA\JsonContent(ref: "#/components/schemas/UnauthorizedResponse")),
            new OA\Response(response: 403, description: "Unauthorized access", content: new OA\JsonContent(ref: "#/components/schemas/ForbiddenResponse")),
            new OA\Response(response: 404, description: "Challenge not found", content: new OA\JsonContent(ref: "#/components/schemas/NotFoundResponse")),
            new OA\Response(response: 422, description: "Business rule failure", content: new OA\JsonContent(ref: "#/components/schemas/ValidationErrorResponse"))
        ]
    )]
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

    #[OA\Delete(
        path: "/challenges/{challenge}/leave",
        summary: "Leave a challenge",
        description: "Removes the authenticated user from a challenge.",
        security: [["bearerAuth" => []]],
        tags: ["Challenges"],
        parameters: [
            new OA\Parameter(
                name: "challenge",
                in: "path",
                required: true,
                description: "The ID of the challenge to leave",
                schema: new OA\Schema(type: "string", format: "uuid")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Left challenge successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Left challenge successfully")
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Unauthenticated", content: new OA\JsonContent(ref: "#/components/schemas/UnauthorizedResponse")),
            new OA\Response(response: 403, description: "Unauthorized access", content: new OA\JsonContent(ref: "#/components/schemas/ForbiddenResponse")),
            new OA\Response(response: 404, description: "Challenge not found", content: new OA\JsonContent(ref: "#/components/schemas/NotFoundResponse")),
            new OA\Response(response: 422, description: "Business rule failure", content: new OA\JsonContent(ref: "#/components/schemas/ValidationErrorResponse"))
        ]
    )]
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
