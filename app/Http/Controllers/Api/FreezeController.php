<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Freeze\StoreFreezeRequest;
use App\Http\Resources\FreezeResource;
use App\Models\User;
use App\Services\FreezeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use RuntimeException;

class FreezeController extends Controller
{
    public function __construct(private readonly FreezeService $freezeService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => ['nullable', Rule::in(['sent', 'received'])],
            'status' => ['nullable', Rule::in(['available', 'used', 'expired'])],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $freezes = $this->freezeService->getHistory(
            $request->user(),
            $validated['type'] ?? null,
            $validated['status'] ?? null,
            (int) ($validated['per_page'] ?? 15)
        );

        return response()->json([
            'status' => true,
            'message' => 'Freezes retrieved successfully',
            'data' => FreezeResource::collection($freezes->items()),
            'meta' => [
                'current_page' => $freezes->currentPage(),
                'last_page' => $freezes->lastPage(),
                'per_page' => $freezes->perPage(),
                'total' => $freezes->total(),
            ],
        ]);
    }

    public function store(StoreFreezeRequest $request): JsonResponse
    {
        $receiver = User::query()->findOrFail($request->validated('receiver_id'));

        try {
            $freeze = $this->freezeService->sendFreeze(
                $request->user(),
                $receiver,
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
            'message' => 'Freeze sent successfully',
            'data' => new FreezeResource($freeze),
        ], 201);
    }
}
