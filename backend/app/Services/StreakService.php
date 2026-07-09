<?php

namespace App\Services;

use App\Events\StreakMilestoneReached;
use App\Models\Streak;
use App\Models\User;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

class StreakService
{
    public function __construct(private readonly FreezeService $freezeService)
    {
    }

    public function updateStreak(User $user, CarbonInterface|string|null $completedDate = null): Streak
    {
        $completedDate = $this->dateString($completedDate ?? now());

        return DB::transaction(function () use ($user, $completedDate): Streak {
            $streak = $this->getOrCreateStreak($user);

            $lastCompletedDate = $streak->last_completed_date?->toDateString();

            if ($lastCompletedDate === $completedDate) {
                return $streak;
            }

            $currentStreak = $lastCompletedDate === CarbonImmutable::parse($completedDate)->subDay()->toDateString()
                ? $streak->current_streak + 1
                : 1;

            $streak->forceFill([
                'current_streak' => $currentStreak,
                'longest_streak' => max($streak->longest_streak, $currentStreak),
                'last_completed_date' => $completedDate,
            ])->save();

            $streak->refresh();

            if (in_array($currentStreak, [7, 30, 100], true)) {
                StreakMilestoneReached::dispatch($streak, $currentStreak);
            }

            return $streak;
        });
    }

    public function calculateStreak(User $user): Streak
    {
        $streak = $this->getOrCreateStreak($user);

        if (
            $streak->last_completed_date !== null
            && $streak->last_completed_date->toDateString() < now()->subDay()->toDateString()
            && $streak->current_streak !== 0
        ) {
            if ($this->freezeService->useFreeze($user) !== null) {
                return $streak->refresh();
            }

            $this->resetStreak($user);

            return $streak->refresh();
        }

        return $streak;
    }

    public function resetStreak(User $user): Streak
    {
        $streak = $this->getOrCreateStreak($user);

        $streak->forceFill([
            'current_streak' => 0,
        ])->save();

        return $streak->refresh();
    }

    public function protectPromiseGap(User $user, CarbonInterface|string $promiseDate, CarbonInterface|string $validationDate): Streak
    {
        $promiseDate = $this->dateString($promiseDate);
        $validationDate = $this->dateString($validationDate);

        return DB::transaction(function () use ($user, $promiseDate, $validationDate): Streak {
            $streak = $this->getOrCreateStreak($user);
            $previousDate = CarbonImmutable::parse($promiseDate)->subDay()->toDateString();

            if ($streak->last_completed_date?->toDateString() === $validationDate && $streak->current_streak <= 1) {
                $streak->forceFill([
                    'current_streak' => 2,
                    'longest_streak' => max($streak->longest_streak, 2),
                ])->save();

                return $streak->refresh();
            }

            if ($streak->last_completed_date?->toDateString() === $previousDate) {
                $protectedStreak = $streak->current_streak + 1;

                $streak->forceFill([
                    'current_streak' => $protectedStreak,
                    'longest_streak' => max($streak->longest_streak, $protectedStreak),
                    'last_completed_date' => $promiseDate,
                ])->save();
            }

            return $this->updateStreak($user, $validationDate);
        });
    }

    public function getStatistics(User $user): Streak
    {
        return $this->calculateStreak($user);
    }

    private function getOrCreateStreak(User $user): Streak
    {
        return Streak::query()->firstOrCreate([
            'user_id' => $user->id,
        ], [
            'current_streak' => 0,
            'longest_streak' => 0,
        ]);
    }

    private function dateString(CarbonInterface|string $date): string
    {
        if ($date instanceof CarbonInterface) {
            return $date->toDateString();
        }

        return CarbonImmutable::parse($date)->toDateString();
    }
}
