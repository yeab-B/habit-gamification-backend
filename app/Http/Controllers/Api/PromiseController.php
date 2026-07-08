<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Promise\StorePromiseRequest;
use App\Http\Resources\PromiseResource;
use App\Services\PromiseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class PromiseController extends Controller
{
    public function __construct(private readonly PromiseService $promiseService)
    {
    }

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
