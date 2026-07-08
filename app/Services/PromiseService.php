<?php

namespace App\Services;

use App\Events\PromiseBroken;
use App\Events\PromiseCreated;
use App\Events\PromiseFulfilled;
use App\Models\Promise;
use App\Models\User;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PromiseService
{
    public function __construct(
        private readonly DailyProgressService $dailyProgressService,
        private readonly StreakService $streakService
    )
    {
    }

    public function createPromise(User $user, ?string $reason = null): Promise
    {
        return DB::transaction(function () use ($user, $reason): Promise {
            $promiseDate = now()->toDateString();

            if ($this->hasPendingPromise($user)) {
                throw new RuntimeException('You already have a pending promise.');
            }

            if (! $this->dailyProgressService->hasRequiredProgress($user)) {
                throw new RuntimeException('You do not have required habits to recover.');
            }

            if ($this->dailyProgressService->hasCompletedRequiredProgress($user, $promiseDate)) {
                throw new RuntimeException('You cannot create a promise after completing today\'s habits.');
            }

            $promise = Promise::query()->create([
                'user_id' => $user->id,
                'promise_date' => $promiseDate,
                'validation_date' => now()->addDay()->toDateString(),
                'status' => 'pending',
                'reason' => $reason,
                'reward_coins' => (int) config('promise.reward_coins', 10),
                'penalty_coins' => (int) config('promise.penalty_coins', 100),
            ]);

            $this->streakService->updateStreak($user, $promiseDate);

            PromiseCreated::dispatch($promise);

            return $promise->load('user');
        });
    }

    public function validatePromise(Promise $promise, CarbonInterface|string|null $validatedAt = null): Promise
    {
        $validatedAt = CarbonImmutable::parse($validatedAt ?? now());

        if ($promise->status !== 'pending') {
            throw new RuntimeException('Promise has already been resolved.');
        }

        if ($validatedAt->toDateString() < $promise->validation_date->toDateString()) {
            throw new RuntimeException('Promise cannot be validated before the validation date.');
        }

        $completed = $this->dailyProgressService->hasCompletedRequiredProgress(
            $promise->user,
            $promise->validation_date
        );

        return $completed
            ? $this->fulfillPromise($promise, $validatedAt)
            : $this->breakPromise($promise, $validatedAt);
    }

    public function fulfillPromise(Promise $promise, CarbonInterface|string|null $validatedAt = null): Promise
    {
        return DB::transaction(function () use ($promise, $validatedAt): Promise {
            $promise->forceFill([
                'status' => 'fulfilled',
                'validated_at' => CarbonImmutable::parse($validatedAt ?? now()),
            ])->save();

            $promise->refresh()->load('user');

            PromiseFulfilled::dispatch($promise);

            return $promise;
        });
    }

    public function breakPromise(Promise $promise, CarbonInterface|string|null $validatedAt = null): Promise
    {
        return DB::transaction(function () use ($promise, $validatedAt): Promise {
            $promise->forceFill([
                'status' => 'broken',
                'validated_at' => CarbonImmutable::parse($validatedAt ?? now()),
            ])->save();

            $promise->refresh()->load('user');

            PromiseBroken::dispatch($promise);

            return $promise;
        });
    }

    public function getPromises(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return Promise::query()
            ->where('user_id', $user->id)
            ->latest('promise_date')
            ->latest()
            ->paginate($perPage);
    }

    private function hasPendingPromise(User $user): bool
    {
        return Promise::query()
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->exists();
    }
}
