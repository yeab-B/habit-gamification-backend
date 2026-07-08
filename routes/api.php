<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AchievementController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ChallengeController;
use App\Http\Controllers\Api\CoinController;
use App\Http\Controllers\Api\DailyProgressController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\FriendController;
use App\Http\Controllers\Api\FreezeController;
use App\Http\Controllers\Api\IncomeController;
use App\Http\Controllers\Api\IncomeSourceController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\PromiseController;
use App\Http\Controllers\Api\StreakController;
use App\Http\Controllers\Api\TaskController;
use App\Finance\Controllers\BudgetController;
use App\Finance\Controllers\ExpenseController;
use App\Finance\Controllers\EmergencyFundController;
use App\Finance\Controllers\InvestmentController;
use App\Finance\Controllers\RewardWalletController;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/google/login', [AuthController::class, 'googleLogin']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

Route::get('/email/verify/{id}/{hash}', function (string $id, string $hash) {
    $user = \App\Models\User::query()->findOrFail($id);

    if (! hash_equals(sha1($user->getEmailForVerification()), $hash)) {
        abort(403);
    }

    if (! $user->hasVerifiedEmail()) {
        $user->markEmailAsVerified();
    }

    return response()->json([
        'status' => true,
        'message' => 'Email verified successfully',
    ]);
})->middleware('signed')->name('verification.verify');

Route::middleware(['auth:sanctum', EnsureEmailIsVerified::class])->group(function (): void {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'update']);
    Route::put('/change-password', [ProfileController::class, 'changePassword']);
    Route::delete('/profile', [ProfileController::class, 'destroy']);
});

Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('/dashboard', [DashboardController::class, 'dashboard']);
    Route::get('/statistics', [DashboardController::class, 'statistics']);
    Route::post('/tasks/{task}/complete', [DailyProgressController::class, 'completeTask']);
    Route::get('/daily-checkins', [DailyProgressController::class, 'checkins']);
    Route::get('/today', [DailyProgressController::class, 'today']);
    Route::get('/streaks', [StreakController::class, 'index']);
    Route::get('/streaks/current', [StreakController::class, 'current']);
    Route::get('/coins', [CoinController::class, 'balance']);
    Route::get('/coin-transactions', [CoinController::class, 'transactions']);
    Route::get('/achievements', [AchievementController::class, 'index']);
    Route::get('/my-achievements', [AchievementController::class, 'myAchievements']);
    Route::get('/users/search', [FriendController::class, 'search']);
    Route::get('/friends', [FriendController::class, 'index']);
    Route::post('/friends/request', [FriendController::class, 'sendRequest']);
    Route::post('/friends/accept', [FriendController::class, 'accept']);
    Route::post('/friends/reject', [FriendController::class, 'reject']);
    Route::delete('/friends/{id}', [FriendController::class, 'destroy']);
    Route::get('/freezes', [FreezeController::class, 'index']);
    Route::post('/freezes', [FreezeController::class, 'store']);
    Route::get('/promises', [PromiseController::class, 'index']);
    Route::post('/promises', [PromiseController::class, 'store']);
    Route::get('/income-sources', [IncomeSourceController::class, 'index']);
    Route::post('/income-sources', [IncomeSourceController::class, 'store']);
    Route::get('/incomes', [IncomeController::class, 'index']);
    Route::post('/incomes', [IncomeController::class, 'store']);
    Route::put('/incomes/{income}', [IncomeController::class, 'update']);
    Route::delete('/incomes/{income}', [IncomeController::class, 'destroy']);

    Route::get('/challenges', [ChallengeController::class, 'index']);
    Route::post('/challenges', [ChallengeController::class, 'store']);
    Route::get('/challenges/{challenge}', [ChallengeController::class, 'show']);
    Route::put('/challenges/{challenge}', [ChallengeController::class, 'update']);
    Route::delete('/challenges/{challenge}', [ChallengeController::class, 'destroy']);
    Route::post('/challenges/{challenge}/join', [ChallengeController::class, 'join']);
    Route::delete('/challenges/{challenge}/leave', [ChallengeController::class, 'leave']);

    Route::get('/categories', [CategoryController::class, 'index']);
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::put('/categories/{category}', [CategoryController::class, 'update']);
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy']);

    Route::get('/tasks', [TaskController::class, 'index']);
    Route::post('/tasks', [TaskController::class, 'store']);
    Route::put('/tasks/{task}', [TaskController::class, 'update']);
    Route::delete('/tasks/{task}', [TaskController::class, 'destroy']);

    // Finance - Budget
    Route::get('/finance/budget', [BudgetController::class, 'show']);
    Route::post('/finance/budget/settings', [BudgetController::class, 'settings']);

    // Finance - Expenses
    Route::get('/finance/expenses', [ExpenseController::class, 'index']);
    Route::post('/finance/expenses', [ExpenseController::class, 'store']);
    Route::put('/finance/expenses/{expense}', [ExpenseController::class, 'update']);
    Route::delete('/finance/expenses/{expense}', [ExpenseController::class, 'destroy']);

    // Finance - Emergency Fund
    Route::get('/finance/emergency-funds', [EmergencyFundController::class, 'index']);
    Route::post('/finance/emergency-funds', [EmergencyFundController::class, 'store']);
    Route::post('/finance/emergency-funds/{fund}/deposit', [EmergencyFundController::class, 'deposit']);
    Route::post('/finance/emergency-funds/{fund}/withdraw', [EmergencyFundController::class, 'withdraw']);

    // Finance - Investments
    Route::get('/finance/investments', [InvestmentController::class, 'index']);
    Route::post('/finance/investments', [InvestmentController::class, 'store']);
    Route::post('/finance/investments/{investment}/transactions', [InvestmentController::class, 'transaction']);

    // Finance - Reward Wallet
    Route::get('/finance/reward-wallet', [RewardWalletController::class, 'show']);
    Route::get('/finance/reward-transactions', [RewardWalletController::class, 'transactions']);
});
