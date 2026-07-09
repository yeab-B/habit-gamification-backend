<?php

namespace App\Services;

use App\Events\IncomeCreated;
use App\Models\Income;
use App\Models\IncomeSource;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class IncomeService
{
    public function __construct(private readonly FinanceCalculationService $financeCalculationService)
    {
    }

    public function getIncomeSources(User $user): Collection
    {
        return IncomeSource::query()
            ->where(function ($query) use ($user): void {
                $query->whereNull('user_id')
                    ->orWhere('user_id', $user->id);
            })
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->get();
    }

    public function createIncomeSource(User $user, array $data): IncomeSource
    {
        return IncomeSource::query()->create([
            'user_id' => $user->id,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'is_default' => false,
        ]);
    }

    public function createIncome(User $user, array $data): Income
    {
        $this->assertSourceAvailableToUser($user, $data['income_source_id']);

        return DB::transaction(function () use ($user, $data): Income {
            $income = Income::query()->create([
                'user_id' => $user->id,
                'income_source_id' => $data['income_source_id'],
                'amount' => $data['amount'],
                'currency' => $data['currency'] ?? 'ETB',
                'income_date' => $data['income_date'],
                'description' => $data['description'] ?? null,
            ])->load('incomeSource');

            IncomeCreated::dispatch($income);

            return $income;
        });
    }

    public function updateIncome(Income $income, array $data): Income
    {
        if (array_key_exists('income_source_id', $data)) {
            $this->assertSourceAvailableToUser($income->user, $data['income_source_id']);
        }

        $income->fill($data)->save();

        return $income->fresh('incomeSource');
    }

    public function deleteIncome(Income $income): void
    {
        $income->delete();
    }

    public function getIncomeHistory(User $user, array $filters = []): LengthAwarePaginator
    {
        return Income::query()
            ->with('incomeSource')
            ->where('user_id', $user->id)
            ->when($filters['source_id'] ?? null, fn ($query, string $sourceId) => $query->where('income_source_id', $sourceId))
            ->when($filters['start_date'] ?? null, fn ($query, string $date) => $query->whereDate('income_date', '>=', $date))
            ->when($filters['end_date'] ?? null, fn ($query, string $date) => $query->whereDate('income_date', '<=', $date))
            ->latest('income_date')
            ->latest()
            ->paginate(15);
    }

    public function getSummary(User $user): array
    {
        $total = (float) Income::query()
            ->where('user_id', $user->id)
            ->sum('amount');

        return [
            'total_income' => number_format($total, 2, '.', ''),
            'income_count' => Income::query()->where('user_id', $user->id)->count(),
            'asrat' => $this->financeCalculationService->calculateAsrat($total)['asrat'],
            'remaining_after_asrat' => $this->financeCalculationService->calculateAsrat($total)['remaining'],
        ];
    }

    public function getIncomeBySource(User $user): Collection
    {
        return Income::query()
            ->where('incomes.user_id', $user->id)
            ->join('income_sources', 'incomes.income_source_id', '=', 'income_sources.id')
            ->groupBy('income_sources.id', 'income_sources.name')
            ->orderBy('income_sources.name')
            ->get([
                'income_sources.id as source_id',
                'income_sources.name as source_name',
                DB::raw('COUNT(incomes.id) as income_count'),
                DB::raw('COALESCE(SUM(incomes.amount), 0) as total_amount'),
            ]);
    }

    public function asratFor(Income $income): array
    {
        return $this->financeCalculationService->calculateAsrat($income->amount);
    }

    private function assertSourceAvailableToUser(User $user, string $sourceId): void
    {
        $exists = IncomeSource::query()
            ->where('id', $sourceId)
            ->where(function ($query) use ($user): void {
                $query->whereNull('user_id')
                    ->orWhere('user_id', $user->id);
            })
            ->exists();

        if (! $exists) {
            throw new RuntimeException('The selected income source is invalid.');
        }
    }
}
