<?php

namespace Tests\Feature;

use App\Models\Achievement;
use App\Models\Challenge;
use App\Models\DailyCheckin;
use App\Models\Streak;
use App\Models\Task;
use App\Models\TaskCompletion;
use App\Models\User;
use App\Models\UserAchievement;
use App\Services\CoinService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DashboardStatisticsTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_user_can_view_dashboard(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-07-08 10:00:00'));

        $user = User::factory()->create(['name' => 'Dashboard User']);
        $this->seedDashboardData($user);

        Sanctum::actingAs($user);

        $this->getJson('/api/dashboard')
            ->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('message', 'Dashboard retrieved successfully')
            ->assertJsonPath('data.user.id', $user->id)
            ->assertJsonPath('data.user.name', 'Dashboard User')
            ->assertJsonStructure([
                'data' => [
                    'user',
                    'current_challenge',
                    'today',
                    'coins',
                    'streak',
                    'weekly_progress',
                    'monthly_progress',
                    'achievement_summary',
                ],
            ]);
    }

    public function test_dashboard_returns_current_challenge(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-07-08 10:00:00'));

        $user = User::factory()->create();
        $challenge = Challenge::query()->create([
            'user_id' => $user->id,
            'title' => '30 Days Fitness',
            'description' => null,
            'duration_days' => 30,
            'start_date' => '2026-07-01',
            'end_date' => '2026-07-30',
            'status' => 'active',
        ]);

        Sanctum::actingAs($user);

        $this->getJson('/api/dashboard')
            ->assertOk()
            ->assertJsonPath('data.current_challenge.challenge_id', $challenge->id)
            ->assertJsonPath('data.current_challenge.title', '30 Days Fitness')
            ->assertJsonPath('data.current_challenge.duration_days', 30);
    }

    public function test_dashboard_returns_todays_progress(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-07-08 10:00:00'));

        $user = User::factory()->create();
        $taskOne = $this->createTask($user, 10);
        $this->createTask($user, 20);
        $this->completeTask($user, $taskOne, '2026-07-08');

        Sanctum::actingAs($user);

        $this->getJson('/api/dashboard')
            ->assertOk()
            ->assertJsonPath('data.today.total_tasks', 2)
            ->assertJsonPath('data.today.completed_tasks', 1)
            ->assertJsonPath('data.today.remaining_tasks', 1)
            ->assertJsonPath('data.today.earned_points', 10)
            ->assertJsonPath('data.today.completion_percentage', 50);
    }

    public function test_dashboard_returns_streak_information(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-07-08 10:00:00'));

        $user = User::factory()->create();
        Streak::query()->create([
            'user_id' => $user->id,
            'current_streak' => 5,
            'longest_streak' => 12,
            'last_completed_date' => '2026-07-08',
        ]);

        Sanctum::actingAs($user);

        $this->getJson('/api/dashboard')
            ->assertOk()
            ->assertJsonPath('data.streak.current_streak', 5)
            ->assertJsonPath('data.streak.longest_streak', 12)
            ->assertJsonPath('data.streak.last_completed_date', '2026-07-08');
    }

    public function test_dashboard_returns_coin_balance(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-07-08 10:00:00'));

        $user = User::factory()->create();
        $coinService = app(CoinService::class);
        $coinService->earnCoins($user, 'task_completion', 15);
        $coinService->bonusCoins($user, 'streak_reward', 20);

        Sanctum::actingAs($user);

        $this->getJson('/api/dashboard')
            ->assertOk()
            ->assertJsonPath('data.coins.current_balance', 35)
            ->assertJsonPath('data.coins.earned_today', 35)
            ->assertJsonPath('data.coins.earned_this_week', 35)
            ->assertJsonPath('data.coins.earned_this_month', 35);
    }

    public function test_dashboard_returns_achievement_summary(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-07-08 10:00:00'));

        $user = User::factory()->create();
        $achievement = Achievement::query()->create([
            'name' => 'First Win',
            'slug' => 'first-win',
            'description' => 'Complete something.',
            'icon' => null,
            'category' => 'progress',
            'condition_type' => 'tasks_completed',
            'condition_value' => 1,
            'reward_coins' => 25,
            'is_active' => true,
        ]);

        UserAchievement::query()->create([
            'user_id' => $user->id,
            'achievement_id' => $achievement->id,
            'earned_at' => now(),
            'reward_coins' => 25,
        ]);

        Sanctum::actingAs($user);

        $this->getJson('/api/dashboard')
            ->assertOk()
            ->assertJsonPath('data.achievement_summary.total_achievements', 1)
            ->assertJsonPath('data.achievement_summary.total_reward_coins', 25)
            ->assertJsonPath('data.achievement_summary.recently_unlocked.0.achievement.name', 'First Win');
    }

    public function test_weekly_statistics_are_correct(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-07-08 10:00:00'));

        $user = User::factory()->create();
        DailyCheckin::query()->create([
            'user_id' => $user->id,
            'date' => '2026-07-07',
            'tasks_completed' => 2,
            'total_points' => 20,
            'is_completed' => true,
        ]);

        Sanctum::actingAs($user);

        $this->getJson('/api/statistics?period=weekly')
            ->assertOk()
            ->assertJsonPath('data.period', 'weekly')
            ->assertJsonPath('data.completion_statistics.active_days', 1)
            ->assertJsonPath('data.completion_statistics.completed_days', 1);
    }

    public function test_monthly_statistics_are_correct(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-07-08 10:00:00'));

        $user = User::factory()->create();
        $task = $this->createTask($user, 10);
        $this->completeTask($user, $task, '2026-07-08');
        DailyCheckin::query()->create([
            'user_id' => $user->id,
            'date' => '2026-07-08',
            'tasks_completed' => 1,
            'total_points' => 10,
            'is_completed' => true,
        ]);

        Sanctum::actingAs($user);

        $this->getJson('/api/statistics?period=monthly')
            ->assertOk()
            ->assertJsonPath('data.period', 'monthly')
            ->assertJsonPath('data.task_statistics.active_tasks', 1)
            ->assertJsonPath('data.task_statistics.completed_tasks', 1)
            ->assertJsonPath('data.task_statistics.earned_points', 10);
    }

    public function test_statistics_respect_authenticated_user(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-07-08 10:00:00'));

        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        DailyCheckin::query()->create([
            'user_id' => $otherUser->id,
            'date' => '2026-07-08',
            'tasks_completed' => 5,
            'total_points' => 50,
            'is_completed' => true,
        ]);

        Sanctum::actingAs($user);

        $this->getJson('/api/statistics')
            ->assertOk()
            ->assertJsonPath('data.completion_statistics.active_days', 0)
            ->assertJsonPath('data.task_statistics.completed_tasks', 0);
    }

    public function test_empty_datasets_are_handled_correctly(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-07-08 10:00:00'));

        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $this->getJson('/api/dashboard')
            ->assertOk()
            ->assertJsonPath('data.current_challenge', null)
            ->assertJsonPath('data.today.total_tasks', 0)
            ->assertJsonPath('data.coins.current_balance', 0)
            ->assertJsonPath('data.streak.current_streak', 0)
            ->assertJsonPath('data.achievement_summary.total_achievements', 0);

        $this->getJson('/api/statistics')
            ->assertOk()
            ->assertJsonPath('data.completion_statistics.active_days', 0)
            ->assertJsonPath('data.coin_statistics.transactions', 0);
    }

    public function test_dashboard_executes_acceptable_number_of_database_queries(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-07-08 10:00:00'));

        $user = User::factory()->create();
        $this->seedDashboardData($user);

        Sanctum::actingAs($user);

        DB::enableQueryLog();

        $this->getJson('/api/dashboard')->assertOk();

        $this->assertLessThanOrEqual(35, count(DB::getQueryLog()));
    }

    public function test_statistics_endpoint_returns_expected_aggregated_values(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-07-08 10:00:00'));

        $user = User::factory()->create();
        $task = $this->createTask($user, 10);
        $this->completeTask($user, $task, '2026-07-08');
        app(CoinService::class)->earnCoins($user, 'task_completion', 10);

        Sanctum::actingAs($user);

        $this->getJson('/api/statistics?period=monthly')
            ->assertOk()
            ->assertJsonPath('data.task_statistics.completed_tasks', 1)
            ->assertJsonPath('data.task_statistics.earned_points', 10)
            ->assertJsonPath('data.coin_statistics.earned', 10)
            ->assertJsonPath('data.coin_statistics.transactions', 1);
    }

    private function seedDashboardData(User $user): void
    {
        Challenge::query()->create([
            'user_id' => $user->id,
            'title' => '30 Days Fitness',
            'description' => null,
            'duration_days' => 30,
            'start_date' => '2026-07-01',
            'end_date' => '2026-07-30',
            'status' => 'active',
        ]);

        $task = $this->createTask($user, 10);
        $this->createTask($user, 20);
        $this->completeTask($user, $task, '2026-07-08');

        DailyCheckin::query()->create([
            'user_id' => $user->id,
            'date' => '2026-07-08',
            'tasks_completed' => 1,
            'total_points' => 10,
            'is_completed' => true,
        ]);

        Streak::query()->create([
            'user_id' => $user->id,
            'current_streak' => 3,
            'longest_streak' => 5,
            'last_completed_date' => '2026-07-08',
        ]);

        app(CoinService::class)->earnCoins($user, 'task_completion', 10);
    }

    private function createTask(User $user, int $points): Task
    {
        return Task::query()->create([
            'user_id' => $user->id,
            'category_id' => \App\Models\Category::factory()->create(['user_id' => $user->id])->id,
            'title' => 'Task ' . $points,
            'description' => null,
            'points' => $points,
            'is_active' => true,
        ]);
    }

    private function completeTask(User $user, Task $task, string $date): void
    {
        TaskCompletion::query()->create([
            'user_id' => $user->id,
            'task_id' => $task->id,
            'completed_at' => Carbon::parse($date . ' 10:00:00'),
            'completion_date' => $date,
        ]);
    }
}
