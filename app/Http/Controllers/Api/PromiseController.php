<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Promise\StorePromiseRequest;
use App\Http\Resources\PromiseResource;
use App\Services\PromiseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;
use RuntimeException;

class PromiseController extends Controller
{
    public function __construct(private readonly PromiseService $promiseService)
    {
    }

    #[OA\Get(
        path: "/promises",
        summary: "List user promises",
        description: "Retrieves a paginated list of user promises to perform their tasks.",
        security: [["bearerAuth" => []]],
        tags: ["Promises"],
        parameters: [
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
                description: "Promises retrieved successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Promises retrieved successfully"),
                        new OA\Property(
                            property: "data",
                            type: "array",
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: "id", type: "string", format: "uuid", example: "9b3d9d30-b9bd-474c-8822-67cc3b922b11"),
                                    new OA\Property(property: "reason", type: "string", nullable: true, example: "I promise to study every day"),
                                    new OA\Property(property: "status", type: "string", example: "pending"),
                                    new OA\Property(property: "created_at", type: "string", format: "date-time", example: "2026-07-09T03:56:49.000000Z")
                                ]
                            )
                        ),
                        new OA\Property(
                            property: "meta",
                            properties: [
                                new OA\Property(property: "current_page", type: "integer", example: 1),
                                new OA\Property(property: "last_page", type: "integer", example: 2),
                                new OA\Property(property: "per_page", type: "integer", example: 15),
                                new OA\Property(property: "total", type: "integer", example: 25)
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Unauthenticated", content: new OA\JsonContent(ref: "#/components/schemas/UnauthorizedResponse"))
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $promises = $this->promiseService->getPromises(
            $request->user(),
            (int) $request->integer('per_page', 15)
        );

        return response()->json([
            'status' => true,
            'message' => 'Promises retrieved successfully',
            'data' => PromiseResource::collection($promises->items()),
            'meta' => [
                'current_page' => $promises->currentPage(),
                'last_page' => $promises->lastPage(),
                'per_page' => $promises->perPage(),
                'total' => $promises->total(),
            ],
        ]);
    }

    #[OA\Post(
        path: "/promises",
        summary: "Create a promise",
        description: "Creates a new promise to perform tasks under penalties if failed.",
        security: [["bearerAuth" => []]],
        tags: ["Promises"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: "reason", type: "string", example: "Study Laravel 12 features.")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Promise created successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Promise created successfully"),
                        new OA\Property(
                            property: "data",
                            properties: [
                                new OA\Property(property: "id", type: "string", format: "uuid", example: "9b3d9d30-b9bd-474c-8822-67cc3b922b11"),
                                new OA\Property(property: "reason", type: "string", example: "Study Laravel 12 features."),
                                new OA\Property(property: "status", type: "string", example: "pending")
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Unauthenticated", content: new OA\JsonContent(ref: "#/components/schemas/UnauthorizedResponse")),
            new OA\Response(response: 422, description: "Validation/Business rule failure", content: new OA\JsonContent(ref: "#/components/schemas/ValidationErrorResponse"))
        ]
    )]
    public function store(StorePromiseRequest $request): JsonResponse
    {
        try {
            $promise = $this->promiseService->createPromise(
                $request->user(),
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
            'message' => 'Promise created successfully',
            'data' => new PromiseResource($promise),
        ], 201);
    }
}
