<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Freeze\StoreFreezeRequest;
use App\Http\Resources\FreezeResource;
use App\Models\User;
use App\Services\FreezeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use OpenApi\Attributes as OA;
use RuntimeException;

class FreezeController extends Controller
{
    public function __construct(private readonly FreezeService $freezeService)
    {
    }

    #[OA\Get(
        path: "/freezes",
        summary: "List freezes",
        description: "Retrieves the user's sent or received streak freezes history.",
        security: [["bearerAuth" => []]],
        tags: ["Freezes"],
        parameters: [
            new OA\Parameter(
                name: "type",
                in: "query",
                required: false,
                description: "Filter by type",
                schema: new OA\Schema(type: "string", enum: ["sent", "received"])
            ),
            new OA\Parameter(
                name: "status",
                in: "query",
                required: false,
                description: "Filter by status",
                schema: new OA\Schema(type: "string", enum: ["available", "used", "expired"])
            ),
            new OA\Parameter(
                name: "per_page",
                in: "query",
                required: false,
                description: "Results per page",
                schema: new OA\Schema(type: "integer", minimum: 1, maximum: 100)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Freezes retrieved successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Freezes retrieved successfully"),
                        new OA\Property(
                            property: "data",
                            type: "array",
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: "id", type: "string", format: "uuid", example: "9b3d9d30-b9bd-474c-8822-67cc3b922b10"),
                                    new OA\Property(property: "sender_id", type: "string", format: "uuid", example: "9b3d9d30-a3bd-474c-8822-67cc3b922a93"),
                                    new OA\Property(property: "receiver_id", type: "string", format: "uuid", example: "9b3d9d30-b3bd-474c-8822-67cc3b922a94"),
                                    new OA\Property(property: "status", type: "string", example: "available"),
                                    new OA\Property(property: "reason", type: "string", example: "Sick leave"),
                                    new OA\Property(property: "created_at", type: "string", format: "date-time", example: "2026-07-09T03:56:49.000000Z")
                                ]
                            )
                        ),
                        new OA\Property(
                            property: "meta",
                            properties: [
                                new OA\Property(property: "current_page", type: "integer", example: 1),
                                new OA\Property(property: "last_page", type: "integer", example: 3),
                                new OA\Property(property: "per_page", type: "integer", example: 15),
                                new OA\Property(property: "total", type: "integer", example: 40)
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Unauthenticated", content: new OA\JsonContent(ref: "#/components/schemas/UnauthorizedResponse")),
            new OA\Response(response: 422, description: "Validation failure", content: new OA\JsonContent(ref: "#/components/schemas/ValidationErrorResponse"))
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => ['nullable', Rule::in(['sent', 'received'])],
            'status' => ['nullable', Rule::in(['available', 'used', 'expired'])],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $freezes = $this->freezeService->getHistory(
            $request->user(),
            $validated['type'] ?? null,
            $validated['status'] ?? null,
            (int) ($validated['per_page'] ?? 15)
        );

        return response()->json([
            'status' => true,
            'message' => 'Freezes retrieved successfully',
            'data' => FreezeResource::collection($freezes->items()),
            'meta' => [
                'current_page' => $freezes->currentPage(),
                'last_page' => $freezes->lastPage(),
                'per_page' => $freezes->perPage(),
                'total' => $freezes->total(),
            ],
        ]);
    }

    #[OA\Post(
        path: "/freezes",
        summary: "Send a streak freeze",
        description: "Sends a streak freeze item to another user (friend) to protect their streak.",
        security: [["bearerAuth" => []]],
        tags: ["Freezes"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["receiver_id", "reason"],
                properties: [
                    new OA\Property(property: "receiver_id", type: "string", format: "uuid", example: "9b3d9d30-b3bd-474c-8822-67cc3b922a94"),
                    new OA\Property(property: "reason", type: "string", example: "Traveling without internet access")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Freeze sent successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Freeze sent successfully"),
                        new OA\Property(
                            property: "data",
                            properties: [
                                new OA\Property(property: "id", type: "string", format: "uuid", example: "9b3d9d30-b9bd-474c-8822-67cc3b922b10"),
                                new OA\Property(property: "sender_id", type: "string", format: "uuid", example: "9b3d9d30-a3bd-474c-8822-67cc3b922a93"),
                                new OA\Property(property: "receiver_id", type: "string", format: "uuid", example: "9b3d9d30-b3bd-474c-8822-67cc3b922a94"),
                                new OA\Property(property: "status", type: "string", example: "available"),
                                new OA\Property(property: "reason", type: "string", example: "Traveling without internet access")
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Unauthenticated", content: new OA\JsonContent(ref: "#/components/schemas/UnauthorizedResponse")),
            new OA\Response(response: 422, description: "Validation/Business rule failure", content: new OA\JsonContent(ref: "#/components/schemas/ValidationErrorResponse"))
        ]
    )]
    public function store(StoreFreezeRequest $request): JsonResponse
    {
        $receiver = User::query()->findOrFail($request->validated('receiver_id'));

        try {
            $freeze = $this->freezeService->sendFreeze(
                $request->user(),
                $receiver,
                $request->validated('reason')
            );
        } catch (RuntimeException $exception) {
            return response()->json([
                'status' => false,
                'message' => $exception->getMessage(),
                'data' => null,
            ], 422);
        }

        return response()->json([
            'status' => true,
            'message' => 'Freeze sent successfully',
            'data' => new FreezeResource($freeze),
        ], 201);
    }
}
