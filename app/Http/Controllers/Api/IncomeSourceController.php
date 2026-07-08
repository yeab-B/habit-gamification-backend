<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\IncomeSourceResource;
use App\Services\IncomeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IncomeSourceController extends Controller
{
    public function __construct(private readonly IncomeService $incomeService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => 'Income sources retrieved successfully',
            'data' => IncomeSourceResource::collection($this->incomeService->getIncomeSources($request->user())),
        ]);
    }

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
