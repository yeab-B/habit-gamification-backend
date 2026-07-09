<?php

namespace App\Http\Controllers\Api;

use OpenApi\Attributes as OA;

class OpenApiSchemas
{
    // ==========================================
    // REUSABLE MODEL SCHEMAS
    // ==========================================

    #[OA\Schema(
        schema: "User",
        type: "object",
        properties: [
            new OA\Property(property: "id", type: "string", format: "uuid", example: "9b3d9d30-b3bd-474c-8822-67cc3b922a94"),
            new OA\Property(property: "name", type: "string", example: "Abebe Kebede"),
            new OA\Property(property: "email", type: "string", format: "email", example: "abebe@example.com"),
            new OA\Property(property: "avatar", type: "string", nullable: true, example: "https://example.com/avatars/abebe.jpg"),
            new OA\Property(property: "email_verified_at", type: "string", format: "date-time", nullable: true, example: "2026-07-09T03:56:49.000000Z"),
            new OA\Property(property: "created_at", type: "string", format: "date-time", example: "2026-07-09T03:56:49.000000Z")
        ]
    )]
    private $user;

    #[OA\Schema(
        schema: "Category",
        type: "object",
        properties: [
            new OA\Property(property: "id", type: "string", format: "uuid", example: "9b3d9d30-b4bd-474c-8822-67cc3b922a95"),
            new OA\Property(property: "name", type: "string", example: "Fitness & Health"),
            new OA\Property(property: "description", type: "string", nullable: true, example: "Workout, health, and diet tasks"),
            new OA\Property(property: "icon", type: "string", nullable: true, example: "dumbbell"),
            new OA\Property(property: "color", type: "string", nullable: true, example: "#EF4444"),
            new OA\Property(property: "created_at", type: "string", format: "date-time", example: "2026-07-09T03:56:49.000000Z"),
            new OA\Property(property: "updated_at", type: "string", format: "date-time", example: "2026-07-09T03:56:49.000000Z")
        ]
    )]
    private $category;

    #[OA\Schema(
        schema: "Task",
        type: "object",
        properties: [
            new OA\Property(property: "id", type: "string", format: "uuid", example: "9b3d9d30-b5bd-474c-8822-67cc3b922a96"),
            new OA\Property(property: "category_id", type: "string", format: "uuid", example: "9b3d9d30-b4bd-474c-8822-67cc3b922a95"),
            new OA\Property(property: "title", type: "string", example: "Read 10 pages"),
            new OA\Property(property: "description", type: "string", nullable: true, example: "Read a personal development book"),
            new OA\Property(property: "points", type: "integer", example: 15),
            new OA\Property(property: "is_completed", type: "boolean", example: false),
            new OA\Property(property: "created_at", type: "string", format: "date-time", example: "2026-07-09T03:56:49.000000Z")
        ]
    )]
    private $task;

    #[OA\Schema(
        schema: "Challenge",
        type: "object",
        properties: [
            new OA\Property(property: "id", type: "string", format: "uuid", example: "9b3d9d30-b6bd-474c-8822-67cc3b922a97"),
            new OA\Property(property: "title", type: "string", example: "30-Day Savings Challenge"),
            new OA\Property(property: "description", type: "string", nullable: true, example: "Complete tasks in category Finance to earn bonus coins"),
            new OA\Property(property: "reward_coins", type: "integer", example: 100),
            new OA\Property(property: "start_date", type: "string", format: "date", example: "2026-07-01"),
            new OA\Property(property: "end_date", type: "string", format: "date", example: "2026-07-30"),
            new OA\Property(property: "is_joined", type: "boolean", example: true),
            new OA\Property(property: "participants_count", type: "integer", example: 42),
            new OA\Property(property: "created_at", type: "string", format: "date-time", example: "2026-07-09T03:56:49.000000Z")
        ]
    )]
    private $challenge;

    #[OA\Schema(
        schema: "Income",
        type: "object",
        properties: [
            new OA\Property(property: "id", type: "string", format: "uuid", example: "9b3d9d30-b7bd-474c-8822-67cc3b922a98"),
            new OA\Property(property: "income_source", type: "object", ref: "#/components/schemas/IncomeSource", nullable: true),
            new OA\Property(property: "amount", type: "number", format: "float", example: 50000.00),
            new OA\Property(property: "currency", type: "string", example: "ETB"),
            new OA\Property(property: "income_date", type: "string", format: "date", example: "2026-07-09"),
            new OA\Property(property: "description", type: "string", nullable: true, example: "Monthly Freelance Consulting Payment"),
            new OA\Property(property: "asrat", type: "number", format: "float", example: 5000.00),
            new OA\Property(property: "remaining_after_asrat", type: "number", format: "float", example: 45000.00),
            new OA\Property(property: "created_at", type: "string", format: "date-time", example: "2026-07-09T03:56:49.000000Z"),
            new OA\Property(property: "updated_at", type: "string", format: "date-time", example: "2026-07-09T03:56:49.000000Z")
        ]
    )]
    private $income;

    #[OA\Schema(
        schema: "IncomeSource",
        type: "object",
        properties: [
            new OA\Property(property: "id", type: "string", format: "uuid", example: "9b3d9d30-b7bd-474c-8822-67cc3b922a99"),
            new OA\Property(property: "name", type: "string", example: "Salary"),
            new OA\Property(property: "description", type: "string", nullable: true, example: "Primary job monthly income"),
            new OA\Property(property: "created_at", type: "string", format: "date-time", example: "2026-07-09T03:56:49.000000Z")
        ]
    )]
    private $incomeSource;

    #[OA\Schema(
        schema: "Expense",
        type: "object",
        properties: [
            new OA\Property(property: "id", type: "string", format: "uuid", example: "9b3d9d30-b8bd-474c-8822-67cc3b922a99"),
            new OA\Property(property: "category", type: "string", example: "Food & Dining"),
            new OA\Property(property: "amount", type: "number", format: "float", example: 4500.00),
            new OA\Property(property: "expense_date", type: "string", format: "date", example: "2026-07-09"),
            new OA\Property(property: "description", type: "string", nullable: true, example: "Groceries from Shola Market"),
            new OA\Property(property: "created_at", type: "string", format: "date-time", example: "2026-07-09T03:56:49.000000Z")
        ]
    )]
    private $expense;

    #[OA\Schema(
        schema: "BudgetAllocation",
        type: "object",
        properties: [
            new OA\Property(property: "asrat_percentage", type: "number", format: "float", example: 10.0),
            new OA\Property(property: "needs_percentage", type: "number", format: "float", example: 50.0),
            new OA\Property(property: "emergency_percentage", type: "number", format: "float", example: 15.0),
            new OA\Property(property: "investment_percentage", type: "number", format: "float", example: 15.0),
            new OA\Property(property: "reward_percentage", type: "number", format: "float", example: 10.0)
        ]
    )]
    private $budgetAllocation;

    #[OA\Schema(
        schema: "EmergencyFund",
        type: "object",
        properties: [
            new OA\Property(property: "id", type: "string", format: "uuid", example: "9b3d9d30-b9bd-474c-8822-67cc3b922b00"),
            new OA\Property(property: "goal_amount", type: "number", format: "float", example: 100000.00),
            new OA\Property(property: "current_amount", type: "number", format: "float", example: 65000.00),
            new OA\Property(property: "status", type: "string", example: "active"),
            new OA\Property(property: "progress_percentage", type: "number", format: "float", example: 65.0)
        ]
    )]
    private $emergencyFund;

    #[OA\Schema(
        schema: "Investment",
        type: "object",
        properties: [
            new OA\Property(property: "id", type: "string", format: "uuid", example: "9b3d9d30-c0bd-474c-8822-67cc3b922b01"),
            new OA\Property(property: "name", type: "string", example: "Commercial Bank of Ethiopia Bond"),
            new OA\Property(property: "type", type: "string", example: "Savings"),
            new OA\Property(property: "description", type: "string", nullable: true, example: "Government Treasury Bond"),
            new OA\Property(property: "total_amount", type: "number", format: "float", example: 75000.00)
        ]
    )]
    private $investment;

    #[OA\Schema(
        schema: "RewardWallet",
        type: "object",
        properties: [
            new OA\Property(property: "id", type: "string", format: "uuid", example: "9b3d9d30-c1bd-474c-8822-67cc3b922b02"),
            new OA\Property(property: "available_balance", type: "number", format: "float", example: 12500.00),
            new OA\Property(property: "locked_balance", type: "number", format: "float", example: 3500.00)
        ]
    )]
    private $rewardWallet;

    #[OA\Schema(
        schema: "Achievement",
        type: "object",
        properties: [
            new OA\Property(property: "id", type: "string", format: "uuid", example: "9b3d9d30-c2bd-474c-8822-67cc3b922b03"),
            new OA\Property(property: "name", type: "string", example: "Asrat Giver"),
            new OA\Property(property: "description", type: "string", example: "Donate first Asrat (tithing) from income"),
            new OA\Property(property: "points_required", type: "integer", example: 100),
            new OA\Property(property: "badge_url", type: "string", nullable: true, example: "https://example.com/badges/asrat.png")
        ]
    )]
    private $achievement;

    #[OA\Schema(
        schema: "Dashboard",
        type: "object",
        properties: [
            new OA\Property(property: "current_streak", type: "integer", example: 7),
            new OA\Property(property: "total_coins", type: "integer", example: 450),
            new OA\Property(property: "tasks_completed", type: "integer", example: 14),
            new OA\Property(property: "tasks_completion_rate", type: "number", format: "float", example: 87.5)
        ]
    )]
    private $dashboard;

    #[OA\Schema(
        schema: "FinanceDashboard",
        type: "object",
        properties: [
            new OA\Property(property: "total_income", type: "number", format: "float", example: 150000.00),
            new OA\Property(property: "total_expenses", type: "number", format: "float", example: 62000.00),
            new OA\Property(property: "budget_remaining", type: "number", format: "float", example: 88000.00),
            new OA\Property(property: "savings_rate", type: "number", format: "float", example: 58.6)
        ]
    )]
    private $financeDashboard;

    // ==========================================
    // REUSABLE REQUEST SCHEMAS
    // ==========================================

    #[OA\Schema(
        schema: "RegisterRequest",
        type: "object",
        required: ["name", "email", "password", "password_confirmation"],
        properties: [
            new OA\Property(property: "name", type: "string", example: "Abebe Kebede"),
            new OA\Property(property: "email", type: "string", format: "email", example: "abebe@example.com"),
            new OA\Property(property: "password", type: "string", format: "password", minLength: 8, example: "SecretPass123!"),
            new OA\Property(property: "password_confirmation", type: "string", format: "password", example: "SecretPass123!")
        ]
    )]
    private $registerRequest;

    #[OA\Schema(
        schema: "LoginRequest",
        type: "object",
        required: ["email", "password"],
        properties: [
            new OA\Property(property: "email", type: "string", format: "email", example: "abebe@example.com"),
            new OA\Property(property: "password", type: "string", format: "password", example: "SecretPass123!")
        ]
    )]
    private $loginRequest;

    #[OA\Schema(
        schema: "UpdateProfileRequest",
        type: "object",
        properties: [
            new OA\Property(property: "name", type: "string", example: "Abebe Kebede"),
            new OA\Property(property: "email", type: "string", format: "email", example: "abebe@example.com")
        ]
    )]
    private $updateProfileRequest;

    #[OA\Schema(
        schema: "ChangePasswordRequest",
        type: "object",
        required: ["current_password", "password", "password_confirmation"],
        properties: [
            new OA\Property(property: "current_password", type: "string", format: "password", example: "SecretPass123!"),
            new OA\Property(property: "password", type: "string", format: "password", minLength: 8, example: "NewSecretPass456!"),
            new OA\Property(property: "password_confirmation", type: "string", format: "password", example: "NewSecretPass456!")
        ]
    )]
    private $changePasswordRequest;

    #[OA\Schema(
        schema: "StoreCategoryRequest",
        type: "object",
        required: ["name"],
        properties: [
            new OA\Property(property: "name", type: "string", example: "Fitness & Health"),
            new OA\Property(property: "description", type: "string", example: "Tasks related to running and gym"),
            new OA\Property(property: "icon", type: "string", example: "dumbbell"),
            new OA\Property(property: "color", type: "string", example: "#EF4444")
        ]
    )]
    private $storeCategoryRequest;

    #[OA\Schema(
        schema: "StoreTaskRequest",
        type: "object",
        required: ["category_id", "title", "points"],
        properties: [
            new OA\Property(property: "category_id", type: "string", format: "uuid", example: "9b3d9d30-b4bd-474c-8822-67cc3b922a95"),
            new OA\Property(property: "title", type: "string", example: "Do 50 pushups"),
            new OA\Property(property: "description", type: "string", example: "Complete 5 sets of 10 pushups"),
            new OA\Property(property: "points", type: "integer", minimum: 0, example: 20)
        ]
    )]
    private $storeTaskRequest;

    #[OA\Schema(
        schema: "StoreChallengeRequest",
        type: "object",
        required: ["title", "duration_days", "category_ids"],
        properties: [
            new OA\Property(property: "title", type: "string", example: "Ultimate Fitness Routine"),
            new OA\Property(property: "description", type: "string", example: "Achieve streak milestones in Fitness & Health category"),
            new OA\Property(property: "duration_days", type: "integer", minimum: 1, example: 30),
            new OA\Property(
                property: "category_ids",
                type: "array",
                items: new OA\Items(type: "string", format: "uuid"),
                example: ["9b3d9d30-b4bd-474c-8822-67cc3b922a95"]
            )
        ]
    )]
    private $storeChallengeRequest;

    #[OA\Schema(
        schema: "StoreIncomeRequest",
        type: "object",
        required: ["income_source_id", "amount", "income_date"],
        properties: [
            new OA\Property(property: "income_source_id", type: "string", format: "uuid", example: "9b3d9d30-b7bd-474c-8822-67cc3b922a99"),
            new OA\Property(property: "amount", type: "number", format: "float", minimum: 0, example: 50000.00),
            new OA\Property(property: "currency", type: "string", minLength: 3, maxLength: 3, example: "ETB"),
            new OA\Property(property: "income_date", type: "string", format: "date", example: "2026-07-09"),
            new OA\Property(property: "description", type: "string", example: "Freelance app development payment")
        ]
    )]
    private $storeIncomeRequest;

    #[OA\Schema(
        schema: "StoreExpenseRequest",
        type: "object",
        required: ["category_id", "amount", "expense_date"],
        properties: [
            new OA\Property(property: "category_id", type: "string", format: "uuid", example: "9b3d9d30-b8bd-474c-8822-67cc3b922a99"),
            new OA\Property(property: "amount", type: "number", format: "float", minimum: 0, example: 1200.00),
            new OA\Property(property: "expense_date", type: "string", format: "date", example: "2026-07-09"),
            new OA\Property(property: "description", type: "string", example: "Lunch with clients")
        ]
    )]
    private $storeExpenseRequest;

    #[OA\Schema(
        schema: "StoreEmergencyFundRequest",
        type: "object",
        required: ["goal_amount"],
        properties: [
            new OA\Property(property: "goal_amount", type: "number", format: "float", minimum: 0, example: 120000.00)
        ]
    )]
    private $storeEmergencyFundRequest;

    #[OA\Schema(
        schema: "StoreInvestmentRequest",
        type: "object",
        required: ["name", "type"],
        properties: [
            new OA\Property(property: "name", type: "string", example: "CBE Gold Bond"),
            new OA\Property(property: "type", type: "string", enum: ["Business", "Stocks", "Crypto", "Education", "Courses", "Books", "Savings"], example: "Savings"),
            new OA\Property(property: "description", type: "string", example: "CBE 5-year treasury coupon bond"),
            new OA\Property(property: "total_amount", type: "number", format: "float", minimum: 0, example: 50000.00)
        ]
    )]
    private $storeInvestmentRequest;

    #[OA\Schema(
        schema: "UpdateBudgetSettingsRequest",
        type: "object",
        properties: [
            new OA\Property(property: "asrat_percentage", type: "number", format: "float", minimum: 0, maximum: 100, example: 10.0),
            new OA\Property(property: "needs_percentage", type: "number", format: "float", minimum: 0, maximum: 100, example: 50.0),
            new OA\Property(property: "emergency_percentage", type: "number", format: "float", minimum: 0, maximum: 100, example: 15.0),
            new OA\Property(property: "investment_percentage", type: "number", format: "float", minimum: 0, maximum: 100, example: 15.0),
            new OA\Property(property: "reward_percentage", type: "number", format: "float", minimum: 0, maximum: 100, example: 10.0)
        ]
    )]
    private $updateBudgetSettingsRequest;

    // ==========================================
    // REUSABLE RESPONSE SCHEMAS
    // ==========================================

    #[OA\Schema(
        schema: "SuccessResponse",
        type: "object",
        properties: [
            new OA\Property(property: "status", type: "boolean", example: true),
            new OA\Property(property: "message", type: "string", example: "Action completed successfully."),
            new OA\Property(property: "data", type: "object", nullable: true)
        ]
    )]
    private $successResponse;

    #[OA\Schema(
        schema: "ValidationErrorResponse",
        type: "object",
        properties: [
            new OA\Property(property: "status", type: "boolean", example: false),
            new OA\Property(property: "message", type: "string", example: "The given data was invalid."),
            new OA\Property(
                property: "errors",
                type: "object",
                properties: [
                    new OA\Property(
                        property: "field_name",
                        type: "array",
                        items: new OA\Items(type: "string"),
                        example: ["The field_name field is required."]
                    )
                ]
            )
        ]
    )]
    private $validationErrorResponse;

    #[OA\Schema(
        schema: "UnauthorizedResponse",
        type: "object",
        properties: [
            new OA\Property(property: "status", type: "boolean", example: false),
            new OA\Property(property: "message", type: "string", example: "Unauthenticated.")
        ]
    )]
    private $unauthorizedResponse;

    #[OA\Schema(
        schema: "NotFoundResponse",
        type: "object",
        properties: [
            new OA\Property(property: "status", type: "boolean", example: false),
            new OA\Property(property: "message", type: "string", example: "Resource not found.")
        ]
    )]
    private $notFoundResponse;

    #[OA\Schema(
        schema: "ForbiddenResponse",
        type: "object",
        properties: [
            new OA\Property(property: "status", type: "boolean", example: false),
            new OA\Property(property: "message", type: "string", example: "This action is unauthorized.")
        ]
    )]
    private $forbiddenResponse;

    #[OA\Schema(
        schema: "ServerErrorResponse",
        type: "object",
        properties: [
            new OA\Property(property: "status", type: "boolean", example: false),
            new OA\Property(property: "message", type: "string", example: "Server error occurred.")
        ]
    )]
    private $serverErrorResponse;
}
