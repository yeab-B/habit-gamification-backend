<?php

namespace App\Http\Controllers\Finance;

use App\Models\Finance\Investment;
use App\Http\Requests\Finance\InvestmentTransactionRequest;
use App\Http\Requests\Finance\StoreInvestmentRequest;
use App\Http\Resources\Finance\InvestmentResource;
use App\Services\Finance\InvestmentService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class InvestmentController extends Controller
{
    public function __construct(private readonly InvestmentService $investmentService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $portfolio = $this->investmentService->getPortfolio($request->user());

        return response()->json([
            'status' => true,
            'message' => 'Investments retrieved successfully',
            'data' => InvestmentResource::collection($portfolio['investments']),
            'portfolio' => ['total_value' => $portfolio['total_value']],
        ]);
    }

    public function store(StoreInvestmentRequest $request): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => 'Investment created successfully',
            'data' => new InvestmentResource($this->investmentService->createInvestment($request->user(), $request->validated())),
        ], 201);
    }

    public function transaction(InvestmentTransactionRequest $request, Investment $investment): JsonResponse
    {
        abort_unless($investment->user_id === $request->user()->id, 403);

        try {
            $result = $this->investmentService->addTransaction($investment, $request->validated());
        } catch (RuntimeException $exception) {
            return response()->json([
                'status' => false,
                'message' => $exception->getMessage(),
            ], 422);
        }

        return response()->json([
            'status' => true,
            'message' => 'Investment transaction recorded successfully',
            'data' => new InvestmentResource($result),
        ]);
    }
}
