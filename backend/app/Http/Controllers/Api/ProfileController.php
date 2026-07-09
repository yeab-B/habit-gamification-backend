<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Http\Requests\Auth\UpdateProfileRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class ProfileController extends Controller
{
    public function __construct(private readonly AuthService $authService)
    {
    }

    #[OA\Get(
        path: "/profile",
        summary: "Get current user profile",
        description: "Retrieves the authenticated user's profile details.",
        security: [["bearerAuth" => []]],
        tags: ["Authentication"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Profile retrieved successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "boolean", example: true),
                        new OA\Property(property: "data", ref: "#/components/schemas/User")
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Unauthenticated", content: new OA\JsonContent(ref: "#/components/schemas/UnauthorizedResponse"))
        ]
    )]
    public function show(Request $request): JsonResponse
    {
        return response()->json([
            'status' => true,
            'data' => new UserResource($request->user()),
        ]);
    }

    #[OA\Put(
        path: "/profile",
        summary: "Update user profile",
        description: "Updates the authenticated user's profile information.",
        security: [["bearerAuth" => []]],
        tags: ["Authentication"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: "#/components/schemas/UpdateProfileRequest")
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Profile updated successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Profile updated successfully"),
                        new OA\Property(property: "data", ref: "#/components/schemas/User")
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Unauthenticated", content: new OA\JsonContent(ref: "#/components/schemas/UnauthorizedResponse")),
            new OA\Response(response: 422, description: "Validation failure", content: new OA\JsonContent(ref: "#/components/schemas/ValidationErrorResponse"))
        ]
    )]
    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $user = $this->authService->updateProfile($request->user(), $request->validated());

        return response()->json([
            'status' => true,
            'message' => 'Profile updated successfully',
            'data' => new UserResource($user),
        ]);
    }

    #[OA\Put(
        path: "/change-password",
        summary: "Change account password",
        description: "Changes the password of the authenticated user.",
        security: [["bearerAuth" => []]],
        tags: ["Authentication"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: "#/components/schemas/ChangePasswordRequest")
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Password changed successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Password changed successfully")
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Unauthenticated", content: new OA\JsonContent(ref: "#/components/schemas/UnauthorizedResponse")),
            new OA\Response(response: 422, description: "Validation failure", content: new OA\JsonContent(ref: "#/components/schemas/ValidationErrorResponse"))
        ]
    )]
    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $this->authService->changePassword($request->user(), $request->validated());

        return response()->json([
            'status' => true,
            'message' => 'Password changed successfully',
        ]);
    }

    #[OA\Delete(
        path: "/profile",
        summary: "Delete account",
        description: "Permanently deletes the authenticated user's account.",
        security: [["bearerAuth" => []]],
        tags: ["Authentication"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Account deleted successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Account deleted successfully")
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Unauthenticated", content: new OA\JsonContent(ref: "#/components/schemas/UnauthorizedResponse"))
        ]
    )]
    public function destroy(Request $request): JsonResponse
    {
        $this->authService->deleteAccount($request->user());

        return response()->json([
            'status' => true,
            'message' => 'Account deleted successfully',
        ]);
    }
}