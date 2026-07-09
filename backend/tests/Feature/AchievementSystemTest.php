<?php

namespace Tests\Feature;

use App\Events\AchievementUnlocked;
use App\Models\Achievement;
use App\Models\Category;
use App\Models\Task;
use App\Models\User;
use App\Models\UserAchievement;
use App\Services\AchievementService;
use Database\Seeders\AchievementSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AchievementSystemTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(AchievementSeeder::class);
    }

    public function test_user_can_list_achievements(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $this->getJson('/api/achievements')
            ->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('message', 'Achievements retrieved successfully')
            ->assertJsonFragment([
                'slug' => 'first-task',
                'condition_type' => 'tasks_completed',
                'condition_value' => 1,
            ]);
    }

    public function test_user_can_view_unlocked_achievements(): void
    {
        $user = User::factory()->create();
        $achievement = Achievement::query()->where('slug', 'first-task')->firstOrFail();

        UserAchievement::query()->create([
            'user_id' => $user->id,
            'achievement_id' => $achievement->id,
            'earned_at' => now(),
            'reward_coins' => $achievement->reward_coins,
        ]);

        Sanctum::actingAs($user);

        $this->getJson('/api/my-achievements')
            ->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.achievement.slug', 'first-task')
            ->assertJsonPath('data.0.reward_coins', 10);
    }

    public function test_first_task_unlocks_achievement(): void
    {
        $user = User::factory()->create();
        $task = $this->createTaskFor($user);

        Sanctum::actingAs($user);

        $this->postJson('/api/tasks/' . $task->id . '/complete')->assertOk();

        $achievement = Achievement::query()->where('slug', 'first-task')->firstOrFail();

        $this->assertDatabaseHas('user_achievements', [
            'user_id' => $user->id,
            'achievement_id' => $achievement->id,
            'reward_coins' => 10,
        ]);
    }

    public function test_duplicate_unlock_is_prevented(): void
    {
        $user = User::factory()->create();
        $achievement = Achievement::query()->where('slug', 'first-task')->firstOrFail();
        $service = app(AchievementService::class);

        $service->unlock($user, $achievement);
        $service->unlock($user, $achievement);

        $this->assertSame(1, UserAchievement::query()
            ->where('user_id', $user->id)
            ->where('achievement_id', $achievement->id)
            ->count());
    }

    public function test_reward_coins_are_added_when_achievement_unlocks(): void
    {
        $user = User::factory()->create();
        $achievement = Achievement::query()->where('slug', 'first-task')->firstOrFail();

        app(AchievementService::class)->unlock($user, $achievement);

        $this->assertDatabaseHas('coin_transactions', [
            'user_id' => $user->id,
            'type' => 'bonus',
            'source' => 'achievement',
            'amount' => 10,
            'balance_after' => 10,
            'description' => 'Achievement unlocked: First Task',
            'reference_id' => $achievement->id,
        ]);
    }

    public function test_achievement_unlocked_event_is_dispatched(): void
    {
        Event::fake([AchievementUnlocked::class]);

        $user = User::factory()->create();
        $achievement = Achievement::query()->where('slug', 'first-task')->firstOrFail();

        app(AchievementService::class)->unlock($user, $achievement);

        Event::assertDispatched(AchievementUnlocked::class);
    }

    public function test_user_only_sees_their_own_unlocked_achievements(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $achievement = Achievement::query()->where('slug', 'first-task')->firstOrFail();

        UserAchievement::query()->create([
            'user_id' => $otherUser->id,
            'achievement_id' => $achievement->id,
            'earned_at' => now(),
            'reward_coins' => $achievement->reward_coins,
        ]);

        Sanctum::actingAs($user);

        $this->getJson('/api/my-achievements')
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    private function createTaskFor(User $user): Task
    {
        $category = Category::factory()->create(['user_id' => $user->id]);

        return Task::query()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'title' => 'Read',
            'description' => null,
            'points' => 10,
            'is_active' => true,
        ]);
    }
}
