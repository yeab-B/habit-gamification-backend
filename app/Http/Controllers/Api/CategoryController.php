<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function __construct(private readonly CategoryService $categoryService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $categories = $this->categoryService->getCategories($request->user());

        return response()->json([
            'status' => true,
            'message' => 'Categories retrieved successfully',
            'data' => CategoryResource::collection($categories),
        ]);
    }

    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $category = $this->categoryService->createCategory($request->user(), $request->validated());

        return response()->json([
            'status' => true,
            'message' => 'Category created successfully',
            'data' => new CategoryResource($category),
        ], 201);
    }

    public function update(UpdateCategoryRequest $request, Category $category): JsonResponse
    {
        $updatedCategory = $this->categoryService->updateCategory($category, $request->validated());

        return response()->json([
            'status' => true,
            'message' => 'Category updated successfully',
            'data' => new CategoryResource($updatedCategory),
        ]);
    }

    public function destroy(Request $request, Category $category): JsonResponse
    {
        if (! $request->user()->can('delete', $category)) {
            return response()->json([
                'status' => false,
                'message' => 'This action is unauthorized.',
                'errors' => [
                    'authorization' => ['You are not allowed to perform this action.'],
                ],
            ], 403);
        }

        $this->categoryService->deleteCategory($category);

        return response()->json([
            'status' => true,
            'message' => 'Category deleted successfully',
            'data' => [],
        ]);
    }
}
