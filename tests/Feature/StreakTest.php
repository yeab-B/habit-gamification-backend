<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Streak;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class StreakTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_user_can_view_streak(): void
    {
        $user = User::factory()->create();

        Streak::query()->create([
            'user_id' => $user->id,
            'current_streak' => 10,
            'longest_streak' => 50,
            'last_completed_date' => now()->toDateString(),
        ]);

        Sanctum::actingAs($user);

        $this->getJson('/api/streaks')
            ->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.current_streak', 10)
            ->assertJsonPath('data.longest_streak', 50);
    }

    public function test_completing_tasks_increases_streak(): void
    {
        $user = User::factory()->create();
        $yesterdayTask = $this->createTaskFor($user, 'Walk');
        $todayTask = $this->createTaskFor($user, 'Read');

        Sanctum::actingAs($user);

        Carbon::setTestNow(Carbon::parse('2026-07-07 09:00:00'));
        $this->postJson('/api/tasks/' . $yesterdayTask->id . '/complete')->assertOk();

        Carbon::setTestNow(Carbon::parse('2026-07-08 09:00:00'));
        $this->postJson('/api/tasks/' . $todayTask->id . '/complete')->assertOk();

        $this->getJson('/api/streaks/current')
            ->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.current_streak', 2);
    }

    public function test_missing_day_resets_streak(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-07-08 09:00:00'));

        $user = User::factory()->create();

        Streak::query()->create([
            'user_id' => $user->id,
            'current_streak' => 3,
            'longest_streak' => 3,
            'last_completed_date' => '2026-07-06',
        ]);

        Sanctum::actingAs($user);

        $this->getJson('/api/streaks')
            ->assertOk()
            ->assertJsonPath('data.current_streak', 0)
            ->assertJsonPath('data.longest_streak', 3);
    }

    public function test_longest_streak_updates(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-07-08 09:00:00'));

        $user = User::factory()->create();
        $task = $this->createTaskFor($user, 'Exercise');

        Streak::query()->create([
            'user_id' => $user->id,
            'current_streak' => 24,
            'longest_streak' => 24,
            'last_completed_date' => '2026-07-07',
        ]);

        Sanctum::actingAs($user);

        $this->postJson('/api/tasks/' . $task->id . '/complete')->assertOk();

        $this->getJson('/api/streaks')
            ->assertOk()
            ->assertJsonPath('data.current_streak', 25)
            ->assertJsonPath('data.longest_streak', 25);
    }

    public function test_user_cannot_see_another_users_streak(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        Streak::query()->create([
            'user_id' => $otherUser->id,
            'current_streak' => 12,
            'longest_streak' => 20,
            'last_completed_date' => now()->toDateString(),
        ]);

        Sanctum::actingAs($user);

        $this->getJson('/api/streaks')
            ->assertOk()
            ->assertJsonPath('data.current_streak', 0)
            ->assertJsonPath('data.longest_streak', 0);
    }

    private function createTaskFor(User $user, string $title): Task
    {
        $category = Category::factory()->create(['user_id' => $user->id]);

        return Task::query()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'title' => $title,
            'description' => null,
            'points' => 10,
            'is_active' => true,
        ]);
    }
}
