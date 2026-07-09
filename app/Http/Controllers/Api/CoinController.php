<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CoinTransactionHistoryRequest;
use App\Http\Resources\CoinBalanceResource;
use App\Http\Resources\CoinTransactionResource;
use App\Services\CoinService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class CoinController extends Controller
{
    public function __construct(private readonly CoinService $coinService)
    {
    }

    #[OA\Get(
        path: "/coins",
        summary: "Get coin balance",
        description: "Retrieves the current coin balance of the authenticated user.",
        security: [["bearerAuth" => []]],
        tags: ["Coins"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Coin balance retrieved successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Coin balance retrieved successfully"),
                        new OA\Property(
                            property: "data",
                            properties: [
                                new OA\Property(property: "balance", type: "integer", example: 450)
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Unauthenticated", content: new OA\JsonContent(ref: "#/components/schemas/UnauthorizedResponse"))
        ]
    )]
    public function balance(Request $request): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => 'Coin balance retrieved successfully',
            'data' => new CoinBalanceResource([
                'balance' => $this->coinService->getBalance($request->user()),
            ]),
        ]);
    }

    #[OA\Get(
        path: "/coin-transactions",
        summary: "List coin transactions",
        description: "Retrieves a paginated list of coin transactions (earns, spends, bonuses, penalties).",
        security: [["bearerAuth" => []]],
        tags: ["Coins"],
        parameters: [
            new OA\Parameter(
                name: "type",
                in: "query",
                required: false,
                description: "Filter transactions by type",
                schema: new OA\Schema(type: "string", enum: ["earn", "spend", "bonus", "penalty"])
            ),
            new OA\Parameter(
                name: "page",
                in: "query",
                required: false,
                description: "Page number",
                schema: new OA\Schema(type: "integer", minimum: 1)
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Coin transactions retrieved successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Coin transactions retrieved successfully"),
                        new OA\Property(
                            property: "data",
                            type: "array",
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: "id", type: "string", format: "uuid", example: "9b3d9d30-b9bd-474c-8822-67cc3b922b08"),
                                    new OA\Property(property: "amount", type: "integer", example: 50),
                                    new OA\Property(property: "type", type: "string", example: "earn"),
                                    new OA\Property(property: "description", type: "string", example: "Completed task: Read 10 pages"),
                                    new OA\Property(property: "created_at", type: "string", format: "date-time", example: "2026-07-09T03:56:49.000000Z")
                                ]
                            )
                        ),
                        new OA\Property(
                            property: "meta",
                            properties: [
                                new OA\Property(property: "current_page", type: "integer", example: 1),
                                new OA\Property(property: "last_page", type: "integer", example: 5),
                                new OA\Property(property: "per_page", type: "integer", example: 15),
                                new OA\Property(property: "total", type: "integer", example: 70)
                            ]
                        )
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Unauthenticated", content: new OA\JsonContent(ref: "#/components/schemas/UnauthorizedResponse")),
            new OA\Response(response: 422, description: "Validation failure", content: new OA\JsonContent(ref: "#/components/schemas/ValidationErrorResponse"))
        ]
    )]
    public function transactions(CoinTransactionHistoryRequest $request): JsonResponse
    {
        $transactions = $this->coinService->getHistory(
            $request->user(),
            $request->validated('type')
        );

        return response()->json([
            'status' => true,
            'message' => 'Coin transactions retrieved successfully',
            'data' => CoinTransactionResource::collection($transactions->items()),
            'meta' => [
                'current_page' => $transactions->currentPage(),
                'last_page' => $transactions->lastPage(),
                'per_page' => $transactions->perPage(),
                'total' => $transactions->total(),
            ],
        ]);
    }
}
