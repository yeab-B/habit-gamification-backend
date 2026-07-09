<?php

namespace Database\Seeders;

use App\Models\Achievement;
use Illuminate\Database\Seeder;

class AchievementSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->achievements() as $achievement) {
            Achievement::query()->updateOrCreate(
                ['slug' => $achievement['slug']],
                $achievement
            );
        }
    }

    private function achievements(): array
    {
        return [
            [
                'name' => 'First Task',
                'slug' => 'first-task',
                'description' => 'Complete your first task.',
                'icon' => 'trophy',
                'category' => 'task',
                'condition_type' => 'tasks_completed',
                'condition_value' => 1,
                'reward_coins' => 10,
                'is_active' => true,
            ],
            [
                'name' => 'Complete 10 Tasks',
                'slug' => 'complete-10-tasks',
                'description' => 'Complete 10 tasks.',
                'icon' => 'check-circle',
                'category' => 'task',
                'condition_type' => 'tasks_completed',
                'condition_value' => 10,
                'reward_coins' => 25,
                'is_active' => true,
            ],
            [
                'name' => 'Complete 50 Tasks',
                'slug' => 'complete-50-tasks',
                'description' => 'Complete 50 tasks.',
                'icon' => 'star',
                'category' => 'task',
                'condition_type' => 'tasks_completed',
                'condition_value' => 50,
                'reward_coins' => 100,
                'is_active' => true,
            ],
            [
                'name' => 'Complete 100 Tasks',
                'slug' => 'complete-100-tasks',
                'description' => 'Complete 100 tasks.',
                'icon' => 'award',
                'category' => 'task',
                'condition_type' => 'tasks_completed',
                'condition_value' => 100,
                'reward_coins' => 250,
                'is_active' => true,
            ],
            [
                'name' => '3 Day Streak',
                'slug' => '3-day-streak',
                'description' => 'Reach a 3 day streak.',
                'icon' => 'flame',
                'category' => 'streak',
                'condition_type' => 'current_streak',
                'condition_value' => 3,
                'reward_coins' => 10,
                'is_active' => true,
            ],
            [
                'name' => '7 Day Streak',
                'slug' => '7-day-streak',
                'description' => 'Reach a 7 day streak.',
                'icon' => 'flame',
                'category' => 'streak',
                'condition_type' => 'current_streak',
                'condition_value' => 7,
                'reward_coins' => 30,
                'is_active' => true,
            ],
            [
                'name' => '30 Day Streak',
                'slug' => '30-day-streak',
                'description' => 'Reach a 30 day streak.',
                'icon' => 'flame',
                'category' => 'streak',
                'condition_type' => 'current_streak',
                'condition_value' => 30,
                'reward_coins' => 150,
                'is_active' => true,
            ],
            [
                'name' => '100 Day Streak',
                'slug' => '100-day-streak',
                'description' => 'Reach a 100 day streak.',
                'icon' => 'flame',
                'category' => 'streak',
                'condition_type' => 'current_streak',
                'condition_value' => 100,
                'reward_coins' => 500,
                'is_active' => true,
            ],
            [
                'name' => 'Earn 100 Coins',
                'slug' => 'earn-100-coins',
                'description' => 'Earn 100 coins.',
                'icon' => 'coins',
                'category' => 'coin',
                'condition_type' => 'coins_earned',
                'condition_value' => 100,
                'reward_coins' => 20,
                'is_active' => true,
            ],
            [
                'name' => 'Earn 500 Coins',
                'slug' => 'earn-500-coins',
                'description' => 'Earn 500 coins.',
                'icon' => 'coins',
                'category' => 'coin',
                'condition_type' => 'coins_earned',
                'condition_value' => 500,
                'reward_coins' => 100,
                'is_active' => true,
            ],
            [
                'name' => 'Earn 1000 Coins',
                'slug' => 'earn-1000-coins',
                'description' => 'Earn 1000 coins.',
                'icon' => 'coins',
                'category' => 'coin',
                'condition_type' => 'coins_earned',
                'condition_value' => 1000,
                'reward_coins' => 250,
                'is_active' => true,
            ],
            [
                'name' => 'Complete First Challenge',
                'slug' => 'complete-first-challenge',
                'description' => 'Complete your first challenge.',
                'icon' => 'medal',
                'category' => 'challenge',
                'condition_type' => 'challenges_completed',
                'condition_value' => 1,
                'reward_coins' => 50,
                'is_active' => true,
            ],
            [
                'name' => 'Complete 10 Challenges',
                'slug' => 'complete-10-challenges',
                'description' => 'Complete 10 challenges.',
                'icon' => 'medal',
                'category' => 'challenge',
                'condition_type' => 'challenges_completed',
                'condition_value' => 10,
                'reward_coins' => 200,
                'is_active' => true,
            ],
            [
                'name' => 'Add First Friend',
                'slug' => 'add-first-friend',
                'description' => 'Add your first friend.',
                'icon' => 'users',
                'category' => 'social',
                'condition_type' => 'friends_count',
                'condition_value' => 1,
                'reward_coins' => 10,
                'is_active' => true,
            ],
            [
                'name' => 'Send First Freeze',
                'slug' => 'send-first-freeze',
                'description' => 'Send your first freeze to a friend.',
                'icon' => 'snowflake',
                'category' => 'social',
                'condition_type' => 'freezes_sent',
                'condition_value' => 1,
                'reward_coins' => 10,
                'is_active' => true,
            ],
            [
                'name' => 'Keep First Promise',
                'slug' => 'keep-first-promise',
                'description' => 'Fulfill your first promise.',
                'icon' => 'shield-check',
                'category' => 'promise',
                'condition_type' => 'promises_fulfilled',
                'condition_value' => 1,
                'reward_coins' => 10,
                'is_active' => true,
            ],
        ];
    }
}
