<?php

namespace App\Http\Controllers\Finance;

use App\Models\Finance\EmergencyFund;
use App\Http\Requests\Finance\FundTransactionRequest;
use App\Http\Requests\Finance\StoreEmergencyFundRequest;
use App\Http\Resources\Finance\EmergencyFundResource;
use App\Services\Finance\EmergencyFundService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;
use RuntimeException;

class EmergencyFundController extends Controller
{
    public function __construct(private readonly EmergencyFundService $emergencyFundService)
    {
    }

    #[OA\Get(
        path: "/finance/emergency-funds",
        summary: "List emergency funds",
        description: "Retrieves a list of emergency funds created by the authenticated user in ETB.",
        security: [["bearerAuth" => []]],
        tags: ["Finance"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Emergency funds retrieved successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Emergency funds retrieved successfully"),
                        new OA\Property(property: "data", type: "array", items: new OA\Items(ref: "#/components/schemas/EmergencyFund"))
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
            'message' => 'Emergency funds retrieved successfully',
            'data' => EmergencyFundResource::collection($this->emergencyFundService->getFunds($request->user())),
        ]);
    }

    #[OA\Post(
        path: "/finance/emergency-funds",
        summary: "Create emergency fund target",
        description: "Creates a new emergency fund goal with target amount in ETB.",
        security: [["bearerAuth" => []]],
        tags: ["Finance"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: "#/components/schemas/StoreEmergencyFundRequest")
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Emergency fund created successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Emergency fund created successfully"),
                        new OA\Property(property: "data", ref: "#/components/schemas/EmergencyFund")
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Unauthenticated", content: new OA\JsonContent(ref: "#/components/schemas/UnauthorizedResponse")),
            new OA\Response(response: 422, description: "Validation failure", content: new OA\JsonContent(ref: "#/components/schemas/ValidationErrorResponse"))
        ]
    )]
    public function store(StoreEmergencyFundRequest $request): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => 'Emergency fund created successfully',
            'data' => new EmergencyFundResource($this->emergencyFundService->createFund($request->user(), $request->validated())),
        ], 201);
    }

    #[OA\Post(
        path: "/finance/emergency-funds/{fund}/deposit",
        summary: "Deposit to emergency fund",
        description: "Deposits money from available balance into the specified emergency fund.",
        security: [["bearerAuth" => []]],
        tags: ["Finance"],
        parameters: [
            new OA\Parameter(name: "fund", in: "path", required: true, description: "The ID of the emergency fund", schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["amount"],
                properties: [
                    new OA\Property(property: "amount", type: "number", format: "float", minimum: 0, example: 5000.00)
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Emergency fund deposit recorded successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Emergency fund deposit recorded successfully"),
                        new OA\Property(property: "data", ref: "#/components/schemas/EmergencyFund")
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Unauthenticated", content: new OA\JsonContent(ref: "#/components/schemas/UnauthorizedResponse")),
            new OA\Response(response: 403, description: "Unauthorized access", content: new OA\JsonContent(ref: "#/components/schemas/ForbiddenResponse")),
            new OA\Response(response: 404, description: "Fund not found", content: new OA\JsonContent(ref: "#/components/schemas/NotFoundResponse")),
            new OA\Response(response: 422, description: "Validation/Business rule failure", content: new OA\JsonContent(ref: "#/components/schemas/ValidationErrorResponse"))
        ]
    )]
    public function deposit(FundTransactionRequest $request, EmergencyFund $fund): JsonResponse
    {
        abort_unless($fund->user_id === $request->user()->id, 403);

        try {
            $result = $this->emergencyFundService->deposit($fund, $request->validated('amount'));
        } catch (RuntimeException $exception) {
            return response()->json([
                'status' => false,
                'message' => $exception->getMessage(),
            ], 422);
        }

        return response()->json([
            'status' => true,
            'message' => 'Emergency fund deposit recorded successfully',
            'data' => new EmergencyFundResource($result),
        ]);
    }

    #[OA\Post(
        path: "/finance/emergency-funds/{fund}/withdraw",
        summary: "Withdraw from emergency fund",
        description: "Withdraws money from the specified emergency fund to available balance.",
        security: [["bearerAuth" => []]],
        tags: ["Finance"],
        parameters: [
            new OA\Parameter(name: "fund", in: "path", required: true, description: "The ID of the emergency fund", schema: new OA\Schema(type: "string", format: "uuid"))
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ["amount"],
                properties: [
                    new OA\Property(property: "amount", type: "number", format: "float", minimum: 0, example: 2500.00)
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Emergency fund withdrawal recorded successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Emergency fund withdrawal recorded successfully"),
                        new OA\Property(property: "data", ref: "#/components/schemas/EmergencyFund")
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Unauthenticated", content: new OA\JsonContent(ref: "#/components/schemas/UnauthorizedResponse")),
            new OA\Response(response: 403, description: "Unauthorized access", content: new OA\JsonContent(ref: "#/components/schemas/ForbiddenResponse")),
            new OA\Response(response: 404, description: "Fund not found", content: new OA\JsonContent(ref: "#/components/schemas/NotFoundResponse")),
            new OA\Response(response: 422, description: "Validation/Business rule failure", content: new OA\JsonContent(ref: "#/components/schemas/ValidationErrorResponse"))
        ]
    )]
    public function withdraw(FundTransactionRequest $request, EmergencyFund $fund): JsonResponse
    {
        abort_unless($fund->user_id === $request->user()->id, 403);

        try {
            $result = $this->emergencyFundService->withdraw($fund, $request->validated('amount'));
        } catch (RuntimeException $exception) {
            return response()->json([
                'status' => false,
                'message' => $exception->getMessage(),
            ], 422);
        }

        return response()->json([
            'status' => true,
            'message' => 'Emergency fund withdrawal recorded successfully',
            'data' => new EmergencyFundResource($result),
        ]);
    }
}
