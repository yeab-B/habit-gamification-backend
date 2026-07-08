<?php

namespace App\Finance\Controllers;

use App\Finance\Models\Investment;
use App\Finance\Requests\InvestmentTransactionRequest;
use App\Finance\Requests\StoreInvestmentRequest;
use App\Finance\Resources\InvestmentResource;
use App\Finance\Services\InvestmentService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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

        return response()->json([
            'status' => true,
            'message' => 'Investment transaction recorded successfully',
            'data' => new InvestmentResource($this->investmentService->addTransaction($investment, $request->validated())),
        ]);
    }
}
