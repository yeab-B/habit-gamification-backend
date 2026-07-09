<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\EmailNotVerifiedException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;
use RuntimeException;

class AuthController extends Controller
{
    public function __construct(private readonly AuthService $authService)
    {
    }

    #[OA\Post(
        path: "/register",
        summary: "Register a new user",
        description: "Creates a new user account and sends a verification email.",
        tags: ["Authentication"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: "#/components/schemas/RegisterRequest")
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Registration successful. Please verify your email.",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Registration successful. Please verify your email."),
                        new OA\Property(
                            property: "data",
                            type: "object",
                            properties: [
                                new OA\Property(property: "user", ref: "#/components/schemas/User"),
                                new OA\Property(property: "token", type: "string", example: "1|abcdef123456...")
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(response: 422, description: "Validation Error", content: new OA\JsonContent(ref: "#/components/schemas/ValidationErrorResponse")),
            new OA\Response(response: 500, description: "Server Error", content: new OA\JsonContent(ref: "#/components/schemas/ServerErrorResponse"))
        ]
    )]
    public function register(RegisterRequest $request): JsonResponse
    {
        $result = $this->authService->register($request->validated());

        return response()->json([
            'status' => true,
            'message' => 'Registration successful. Please verify your email.',
            'data' => [
                'user' => new UserResource($result['user']),
                'token' => $result['token'],
            ],
        ], 201);
    }

    #[OA\Post(
        path: "/login",
        summary: "User login",
        description: "Authenticates user and returns access token.",
        tags: ["Authentication"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: "#/components/schemas/LoginRequest")
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Login successful",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Login successful"),
                        new OA\Property(
                            property: "data",
                            type: "object",
                            properties: [
                                new OA\Property(property: "user", ref: "#/components/schemas/User"),
                                new OA\Property(property: "token", type: "string", example: "1|abcdef123456...")
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(
                response: 403,
                description: "Email not verified",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "boolean", example: false),
                        new OA\Property(property: "message", type: "string", example: "Your email address is not verified.")
                    ]
                )
            ),
            new OA\Response(response: 422, description: "Invalid credentials", content: new OA\JsonContent(ref: "#/components/schemas/ValidationErrorResponse")),
            new OA\Response(response: 500, description: "Server Error", content: new OA\JsonContent(ref: "#/components/schemas/ServerErrorResponse"))
        ]
    )]
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $result = $this->authService->login($request->validated());
        } catch (EmailNotVerifiedException $exception) {
            return response()->json([
                'status' => false,
                'message' => $exception->getMessage(),
            ], 403);
        } catch (RuntimeException $exception) {
            return response()->json([
                'status' => false,
                'message' => $exception->getMessage(),
            ], 422);
        }

        return response()->json([
            'status' => true,
            'message' => 'Login successful',
            'data' => [
                'user' => new UserResource($result['user']),
                'token' => $result['token'],
            ],
        ]);
    }

    #[OA\Post(
        path: "/google/login",
        summary: "Login with Google",
        description: "Authenticates user using a Google OAuth ID token.",
        tags: ["Authentication"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["token"],
                properties: [
                    new OA\Property(property: "token", type: "string", example: "eyJhbGciOiJSUzI1NiIsImtpZCI6IjFh...")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Login successful",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Login successful"),
                        new OA\Property(
                            property: "data",
                            type: "object",
                            properties: [
                                new OA\Property(property: "user", ref: "#/components/schemas/User"),
                                new OA\Property(property: "token", type: "string", example: "1|abcdef123456...")
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(response: 422, description: "Invalid Google Token", content: new OA\JsonContent(ref: "#/components/schemas/ValidationErrorResponse")),
            new OA\Response(response: 500, description: "Server Error", content: new OA\JsonContent(ref: "#/components/schemas/ServerErrorResponse"))
        ]
    )]
    public function googleLogin(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string'],
        ]);

        try {
            $result = $this->authService->googleLogin($validated['token']);
        } catch (RuntimeException $exception) {
            return response()->json([
                'status' => false,
                'message' => $exception->getMessage(),
            ], 422);
        }

        return response()->json([
            'status' => true,
            'message' => 'Login successful',
            'data' => [
                'user' => new UserResource($result['user']),
                'token' => $result['token'],
            ],
        ]);
    }

    #[OA\Post(
        path: "/logout",
        summary: "User logout",
        description: "Revokes current access token.",
        security: [["bearerAuth" => []]],
        tags: ["Authentication"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Logout successful",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Logout successful")
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Unauthenticated", content: new OA\JsonContent(ref: "#/components/schemas/UnauthorizedResponse"))
        ]
    )]
    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return response()->json([
            'status' => true,
            'message' => 'Logout successful',
        ]);
    }

    #[OA\Post(
        path: "/forgot-password",
        summary: "Request password reset",
        description: "Sends a password reset link to user email address.",
        tags: ["Authentication"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["email"],
                properties: [
                    new OA\Property(property: "email", type: "string", format: "email", example: "abebe@example.com")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Password reset link sent",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Password reset link sent if the email exists.")
                    ]
                )
            ),
            new OA\Response(response: 422, description: "Invalid email", content: new OA\JsonContent(ref: "#/components/schemas/ValidationErrorResponse"))
        ]
    )]
    public function forgotPassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
        ]);

        $this->authService->sendPasswordResetLink($validated['email']);

        return response()->json([
            'status' => true,
            'message' => 'Password reset link sent if the email exists.',
        ]);
    }

    #[OA\Post(
        path: "/reset-password",
        summary: "Reset password",
        description: "Updates user password using validation token.",
        tags: ["Authentication"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["email", "token", "password", "password_confirmation"],
                properties: [
                    new OA\Property(property: "email", type: "string", format: "email", example: "abebe@example.com"),
                    new OA\Property(property: "token", type: "string", example: "abcdef123456..."),
                    new OA\Property(property: "password", type: "string", format: "password", minLength: 8, example: "NewSecretPass456!"),
                    new OA\Property(property: "password_confirmation", type: "string", format: "password", example: "NewSecretPass456!")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Password reset successful",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Password reset successful")
                    ]
                )
            ),
            new OA\Response(response: 422, description: "Validation failure", content: new OA\JsonContent(ref: "#/components/schemas/ValidationErrorResponse"))
        ]
    )]
    public function resetPassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'token' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $this->authService->resetPassword($validated);

        return response()->json([
            'status' => true,
            'message' => 'Password reset successful',
        ]);
    }
}