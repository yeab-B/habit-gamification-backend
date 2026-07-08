<?php

namespace App\Services;

use App\Models\Challenge;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ChallengeService
{
    public function getChallenges(User $user): Collection
    {
        return Challenge::query()
            ->with('user')
            ->withCount(['categories', 'participants'])
            ->latest()
            ->get();
    }

    public function getChallenge(Challenge $challenge): Challenge
    {
        return $challenge->load(['user', 'categories', 'participants'])->loadCount(['categories', 'participants']);
    }

    public function createChallenge(User $user, array $data): Challenge
    {
        return DB::transaction(function () use ($user, $data): Challenge {
            $challenge = Challenge::query()->create([
                'user_id' => $user->id,
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'duration_days' => $data['duration_days'],
                'start_date' => $data['start_date'] ?? now()->toDateString(),
                'end_date' => $data['end_date'] ?? now()->addDays((int) $data['duration_days'])->toDateString(),
                'status' => 'active',
            ]);

            $challenge->categories()->sync($data['category_ids']);

            return $challenge->load(['user', 'categories', 'participants'])->loadCount(['categories', 'participants']);
        });
    }

    public function updateChallenge(Challenge $challenge, array $data): Challenge
    {
        DB::transaction(function () use ($challenge, $data): void {
            $attributes = [];

            if (array_key_exists('title', $data)) {
                $attributes['title'] = $data['title'];
            }

            if (array_key_exists('description', $data)) {
                $attributes['description'] = $data['description'];
            }

            if (array_key_exists('duration_days', $data)) {
                $attributes['duration_days'] = $data['duration_days'];
            }

            $challenge->fill($attributes)->save();

            if (array_key_exists('category_ids', $data)) {
                $challenge->categories()->sync($data['category_ids']);
            }
        });

        return $challenge->fresh()->load(['user', 'categories', 'participants'])->loadCount(['categories', 'participants']);
    }

    public function deleteChallenge(Challenge $challenge): void
    {
        DB::transaction(function () use ($challenge): void {
            $challenge->categories()->detach();
            $challenge->participants()->detach();
            $challenge->delete();
        });
    }

    public function joinChallenge(Challenge $challenge, User $user): void
    {
        if ($challenge->participants()->where('user_id', $user->id)->exists()) {
            throw new RuntimeException('You already joined this challenge.');
        }

        $challenge->participants()->attach($user->id, [
            'joined_at' => now(),
        ]);
    }

    public function leaveChallenge(Challenge $challenge, User $user): void
    {
        if ($challenge->user_id === $user->id) {
            throw new RuntimeException('Challenge owner cannot leave their own challenge.');
        }

        $challenge->participants()->detach($user->id);
    }
}
