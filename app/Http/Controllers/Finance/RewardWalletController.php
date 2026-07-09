<?php

namespace App\Http\Controllers\Finance;

use App\Http\Resources\Finance\RewardWalletResource;
use App\Services\Finance\RewardWalletService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RewardWalletController extends Controller
{
    public function __construct(private readonly RewardWalletService $rewardWalletService)
    {
    }

    public function show(Request $request): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => 'Reward wallet retrieved successfully',
            'data' => new RewardWalletResource($this->rewardWalletService->getWallet($request->user())),
        ]);
    }

    public function transactions(Request $request): JsonResponse
    {
        $transactions = $this->rewardWalletService->getTransactions($request->user());

        return response()->json([
            'status' => true,
            'message' => 'Reward transactions retrieved successfully',
            'data' => $transactions->items(),
        ]);
    }
}
