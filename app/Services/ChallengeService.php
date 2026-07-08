<?php

namespace App\Services;

use App\Models\Challenge;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
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

            $this->syncChallengeCategories($challenge, $data['category_ids']);

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
                $this->syncChallengeCategories($challenge, $data['category_ids']);
            }
        });

        return $challenge->fresh()->load(['user', 'categories', 'participants'])->loadCount(['categories', 'participants']);
    }

    public function deleteChallenge(Challenge $challenge): void
    {
        DB::transaction(function () use ($challenge): void {
            DB::table('challenge_categories')->where('challenge_id', $challenge->id)->delete();
            DB::table('challenge_users')->where('challenge_id', $challenge->id)->delete();
            $challenge->delete();
        });
    }

    public function joinChallenge(Challenge $challenge, User $user): void
    {
        if ($challenge->participants()->where('user_id', $user->id)->exists()) {
            throw new RuntimeException('You already joined this challenge.');
        }

        DB::table('challenge_users')->insert([
            'id' => (string) Str::uuid(),
            'challenge_id' => $challenge->id,
            'user_id' => $user->id,
            'joined_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function leaveChallenge(Challenge $challenge, User $user): void
    {
        if ($challenge->user_id === $user->id) {
            throw new RuntimeException('Challenge owner cannot leave their own challenge.');
        }

        DB::table('challenge_users')
            ->where('challenge_id', $challenge->id)
            ->where('user_id', $user->id)
            ->delete();
    }

    private function syncChallengeCategories(Challenge $challenge, array $categoryIds): void
    {
        DB::table('challenge_categories')->where('challenge_id', $challenge->id)->delete();

        $now = now();
        $rows = array_map(static fn (string $categoryId) => [
            'id' => (string) Str::uuid(),
            'challenge_id' => $challenge->id,
            'category_id' => $categoryId,
            'created_at' => $now,
            'updated_at' => $now,
        ], $categoryIds);

        if ($rows !== []) {
            DB::table('challenge_categories')->insert($rows);
        }
    }
}
