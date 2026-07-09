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
use OpenApi\Attributes as OA;

class CategoryController extends Controller
{
    public function __construct(private readonly CategoryService $categoryService)
    {
    }

    #[OA\Get(
        path: "/categories",
        summary: "List user categories",
        description: "Retrieves a list of all categories created by/for the authenticated user.",
        security: [["bearerAuth" => []]],
        tags: ["Categories"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Categories retrieved successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Categories retrieved successfully"),
                        new OA\Property(
                            property: "data",
                            type: "array",
                            items: new OA\Items(ref: "#/components/schemas/Category")
                        )
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Unauthenticated", content: new OA\JsonContent(ref: "#/components/schemas/UnauthorizedResponse"))
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $categories = $this->categoryService->getCategories($request->user());

        return response()->json([
            'status' => true,
            'message' => 'Categories retrieved successfully',
            'data' => CategoryResource::collection($categories),
        ]);
    }

    #[OA\Post(
        path: "/categories",
        summary: "Create a category",
        description: "Creates a new category for tasks.",
        security: [["bearerAuth" => []]],
        tags: ["Categories"],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: "#/components/schemas/StoreCategoryRequest")
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: "Category created successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Category created successfully"),
                        new OA\Property(property: "data", ref: "#/components/schemas/Category")
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Unauthenticated", content: new OA\JsonContent(ref: "#/components/schemas/UnauthorizedResponse")),
            new OA\Response(response: 422, description: "Validation failure", content: new OA\JsonContent(ref: "#/components/schemas/ValidationErrorResponse"))
        ]
    )]
    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $category = $this->categoryService->createCategory($request->user(), $request->validated());

        return response()->json([
            'status' => true,
            'message' => 'Category created successfully',
            'data' => new CategoryResource($category),
        ], 201);
    }

    #[OA\Put(
        path: "/categories/{category}",
        summary: "Update category",
        description: "Updates an existing category.",
        security: [["bearerAuth" => []]],
        tags: ["Categories"],
        parameters: [
            new OA\Parameter(
                name: "category",
                in: "path",
                required: true,
                description: "The ID of the category",
                schema: new OA\Schema(type: "string", format: "uuid")
            )
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: "#/components/schemas/StoreCategoryRequest")
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: "Category updated successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Category updated successfully"),
                        new OA\Property(property: "data", ref: "#/components/schemas/Category")
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Unauthenticated", content: new OA\JsonContent(ref: "#/components/schemas/UnauthorizedResponse")),
            new OA\Response(response: 403, description: "Unauthorized access", content: new OA\JsonContent(ref: "#/components/schemas/ForbiddenResponse")),
            new OA\Response(response: 404, description: "Category not found", content: new OA\JsonContent(ref: "#/components/schemas/NotFoundResponse")),
            new OA\Response(response: 422, description: "Validation failure", content: new OA\JsonContent(ref: "#/components/schemas/ValidationErrorResponse"))
        ]
    )]
    public function update(UpdateCategoryRequest $request, Category $category): JsonResponse
    {
        $updatedCategory = $this->categoryService->updateCategory($category, $request->validated());

        return response()->json([
            'status' => true,
            'message' => 'Category updated successfully',
            'data' => new CategoryResource($updatedCategory),
        ]);
    }

    #[OA\Delete(
        path: "/categories/{category}",
        summary: "Delete category",
        description: "Deletes a category and its associated tasks.",
        security: [["bearerAuth" => []]],
        tags: ["Categories"],
        parameters: [
            new OA\Parameter(
                name: "category",
                in: "path",
                required: true,
                description: "The ID of the category",
                schema: new OA\Schema(type: "string", format: "uuid")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Category deleted successfully",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: "status", type: "boolean", example: true),
                        new OA\Property(property: "message", type: "string", example: "Category deleted successfully"),
                        new OA\Property(property: "data", type: "array", items: new OA\Items(type: "string"), example: [])
                    ]
                )
            ),
            new OA\Response(response: 401, description: "Unauthenticated", content: new OA\JsonContent(ref: "#/components/schemas/UnauthorizedResponse")),
            new OA\Response(response: 403, description: "Unauthorized access", content: new OA\JsonContent(ref: "#/components/schemas/ForbiddenResponse")),
            new OA\Response(response: 404, description: "Category not found", content: new OA\JsonContent(ref: "#/components/schemas/NotFoundResponse"))
        ]
    )]
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
