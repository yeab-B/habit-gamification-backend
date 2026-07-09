<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\IncomeSourceResource;
use App\Services\IncomeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class IncomeSourceController extends Controller
{
    public function __construct(private readonly IncomeService $incomeService)
    {
    }

    #[OA\Get(
        path: "/income-sources",
        summary: "List income sources",
        description: "Retrieves a list of all income sources (e.g. Salary, Freelance) for the authenticated user.",
        security: [["bearerAuth" => []]],
        tags: ["Finance"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Income sources retrieved successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Income sources retrieved successfully"),
                        new OA\Property(
                            property: "data",
                            type: "array",
                            items: new OA\Items(ref: "#/components/schemas/IncomeSource")
                        )
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Unauthenticated", content: new OA\JsonContent(ref: "#/components/schemas/UnauthorizedResponse"))
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => 'Income sources retrieved successfully',
            'data' => IncomeSourceResource::collection($this->incomeService->getIncomeSources($request->user())),
        ]);
    }

    #[OA\Post(
        path: "/income-sources",
        summary: "Create income source",
        description: "Creates a new income source.",
        security: [["bearerAuth" => []]],
        tags: ["Finance"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["name"],
                properties: [
                    new OA\Property(property: "name", type: "string", example: "Freelance"),
                    new OA\Property(property: "description", type: "string", example: "Contracts and client consultancies")
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Income source created successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Income source created successfully"),
                        new OA\Property(property: "data", ref: "#/components/schemas/IncomeSource")
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Unauthenticated", content: new OA\JsonContent(ref: "#/components/schemas/UnauthorizedResponse")),
            new OA\Response(response: 422, description: "Validation failure", content: new OA\JsonContent(ref: "#/components/schemas/ValidationErrorResponse"))
        ]
    )]
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Income source created successfully',
            'data' => new IncomeSourceResource($this->incomeService->createIncomeSource($request->user(), $data)),
        ], 201);
    }
}
