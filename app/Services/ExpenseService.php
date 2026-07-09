<?php

namespace App\Finance\Services;

use App\Finance\Events\ExpenseCreated;
use App\Finance\Models\Expense;
use App\Finance\Models\ExpenseCategory;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use RuntimeException;

class ExpenseService
{
    public function getCategories(User $user)
    {
        return ExpenseCategory::query()
            ->whereNull('user_id')
            ->orWhere('user_id', $user->id)
            ->orderBy('name')
            ->get();
    }

    public function createExpense(User $user, array $data): Expense
    {
        $this->assertCategoryAvailable($user, $data['category_id']);

        $expense = Expense::query()->create([
            'user_id' => $user->id,
            'category_id' => $data['category_id'],
            'amount' => $data['amount'],
            'expense_date' => $data['expense_date'],
            'description' => $data['description'] ?? null,
        ])->load('category');

        ExpenseCreated::dispatch($expense);

        return $expense;
    }

    public function updateExpense(Expense $expense, array $data): Expense
    {
        if (isset($data['category_id'])) {
            $this->assertCategoryAvailable($expense->user, $data['category_id']);
        }

        $expense->fill($data)->save();

        return $expense->fresh('category');
    }

    public function deleteExpense(Expense $expense): void
    {
        $expense->delete();
    }

    public function getExpenses(User $user): LengthAwarePaginator
    {
        return Expense::query()
            ->with('category')
            ->where('user_id', $user->id)
            ->latest('expense_date')
            ->paginate(15);
    }

    public function calculateSpending(User $user): array
    {
        $month = now()->format('Y-m');
        $start = CarbonImmutable::now()->startOfMonth()->toDateString();
        $end = CarbonImmutable::now()->endOfMonth()->toDateString();
        $spent = (float) Expense::query()
            ->where('user_id', $user->id)
            ->whereBetween('expense_date', [$start, $end])
            ->sum('amount');

        return [
            'month' => $month,
            'spent' => number_format($spent, 2, '.', ''),
        ];
    }

    private function assertCategoryAvailable(User $user, string $categoryId): void
    {
        $exists = ExpenseCategory::query()
            ->where('id', $categoryId)
            ->where(fn ($query) => $query->whereNull('user_id')->orWhere('user_id', $user->id))
            ->exists();

        if (! $exists) {
            throw new RuntimeException('The selected expense category is invalid.');
        }
    }
}
