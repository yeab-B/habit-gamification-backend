<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CoinTransactionHistoryRequest;
use App\Http\Resources\CoinBalanceResource;
use App\Http\Resources\CoinTransactionResource;
use App\Services\CoinService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CoinController extends Controller
{
    public function __construct(private readonly CoinService $coinService)
    {
    }

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
