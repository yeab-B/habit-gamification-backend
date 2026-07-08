<?php

namespace App\Finance\Controllers;

use App\Finance\Models\EmergencyFund;
use App\Finance\Requests\FundTransactionRequest;
use App\Finance\Requests\StoreEmergencyFundRequest;
use App\Finance\Resources\EmergencyFundResource;
use App\Finance\Services\EmergencyFundService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class EmergencyFundController extends Controller
{
    public function __construct(private readonly EmergencyFundService $emergencyFundService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => 'Emergency funds retrieved successfully',
            'data' => EmergencyFundResource::collection($this->emergencyFundService->getFunds($request->user())),
        ]);
    }

    public function store(StoreEmergencyFundRequest $request): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => 'Emergency fund created successfully',
            'data' => new EmergencyFundResource($this->emergencyFundService->createFund($request->user(), $request->validated())),
        ], 201);
    }

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
