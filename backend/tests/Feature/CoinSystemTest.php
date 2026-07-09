<?php

namespace Tests\Feature;

use App\Events\ChallengeCompleted;
use App\Models\Category;
use App\Models\Challenge;
use App\Models\CoinTransaction;
use App\Models\Streak;
use App\Models\Task;
use App\Models\User;
use App\Services\CoinService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use RuntimeException;
use Tests\TestCase;

class CoinSystemTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_user_can_view_balance(): void
    {
        $user = User::factory()->create();
        $coinService = app(CoinService::class);

        $coinService->earnCoins($user, 'task_completion', 10, 'Task completed');
        $coinService->bonusCoins($user, 'challenge_completion', 50, 'Challenge completed');
        $coinService->spendCoins($user, 'freeze', 5, 'Freeze donation');

        Sanctum::actingAs($user);

        $this->getJson('/api/coins')
            ->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.balance', 55);
    }

    public function test_balance_calculated_correctly(): void
    {
        $user = User::factory()->create();
        $coinService = app(CoinService::class);

        $coinService->earnCoins($user, 'task_completion', 10);
        $coinService->earnCoins($user, 'task_completion', 20);
        $coinService->spendCoins($user, 'freeze', 5);
        $coinService->bonusCoins($user, 'challenge_completion', 50);

        $this->assertSame(75, $coinService->getBalance($user));
    }

    public function test_transaction_history_loads(): void
    {
        $user = User::factory()->create();

        app(CoinService::class)->earnCoins($user, 'task_completion', 10, 'Task completed');

        Sanctum::actingAs($user);

        $this->getJson('/api/coin-transactions')
            ->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.0.type', 'earn')
            ->assertJsonPath('data.0.source', 'task_completion')
            ->assertJsonPath('data.0.amount', 10)
            ->assertJsonPath('data.0.description', 'Task completed');
    }

    public function test_transaction_history_pagination_works(): void
    {
        $user = User::factory()->create();
        $coinService = app(CoinService::class);

        for ($i = 1; $i <= 16; $i++) {
            $coinService->earnCoins($user, 'task_completion', 1, 'Task completed');
        }

        Sanctum::actingAs($user);

        $this->getJson('/api/coin-transactions?page=2')
            ->assertOk()
            ->assertJsonPath('meta.current_page', 2)
            ->assertJsonPath('meta.total', 16)
            ->assertJsonCount(1, 'data');
    }

    public function test_transaction_history_type_filtering_works(): void
    {
        $user = User::factory()->create();
        $coinService = app(CoinService::class);

        $coinService->earnCoins($user, 'task_completion', 10);
        $coinService->bonusCoins($user, 'streak_reward', 20);

        Sanctum::actingAs($user);

        $this->getJson('/api/coin-transactions?type=bonus')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.type', 'bonus')
            ->assertJsonPath('data.0.source', 'streak_reward');
    }

    public function test_completing_task_creates_earn_transaction(): void
    {
        $user = User::factory()->create();
        $task = $this->createTaskFor($user, 'Exercise', 10);

        Sanctum::actingAs($user);

        $this->postJson('/api/tasks/' . $task->id . '/complete')->assertOk();

        $this->assertDatabaseHas('coin_transactions', [
            'user_id' => $user->id,
            'type' => 'earn',
            'source' => 'task_completion',
            'amount' => 10,
            'balance_after' => 10,
            'description' => 'Task completed',
        ]);
    }

    public function test_streak_reward_creates_bonus_transaction(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-07-08 09:00:00'));

        $user = User::factory()->create();
        $task = $this->createTaskFor($user, 'Read', 10);

        Streak::query()->create([
            'user_id' => $user->id,
            'current_streak' => 6,
            'longest_streak' => 6,
            'last_completed_date' => '2026-07-07',
        ]);

        Sanctum::actingAs($user);

        $this->postJson('/api/tasks/' . $task->id . '/complete')->assertOk();

        $this->assertDatabaseHas('coin_transactions', [
            'user_id' => $user->id,
            'type' => 'bonus',
            'source' => 'streak_reward',
            'amount' => 20,
            'description' => '7 day streak bonus',
        ]);

        $this->getJson('/api/coins')
            ->assertOk()
            ->assertJsonPath('data.balance', 30);
    }

    public function test_challenge_completion_creates_bonus_transaction(): void
    {
        $user = User::factory()->create();
        $challenge = Challenge::query()->create([
            'user_id' => $user->id,
            'title' => '30 Days Healthy Life',
            'description' => null,
            'duration_days' => 30,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(30)->toDateString(),
            'status' => 'active',
        ]);

        ChallengeCompleted::dispatch($challenge, $user);

        $this->assertDatabaseHas('coin_transactions', [
            'user_id' => $user->id,
            'type' => 'bonus',
            'source' => 'challenge_completion',
            'amount' => 50,
            'balance_after' => 50,
            'reference_id' => $challenge->id,
        ]);
    }

    public function test_spending_more_than_balance_handled_correctly(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Insufficient coin balance.');

        app(CoinService::class)->spendCoins(
            User::factory()->create(),
            'freeze',
            3,
            'Freeze donation'
        );
    }

    public function test_transactions_belong_to_correct_user(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        app(CoinService::class)->earnCoins($otherUser, 'task_completion', 100, 'Task completed');

        Sanctum::actingAs($user);

        $this->getJson('/api/coins')
            ->assertOk()
            ->assertJsonPath('data.balance', 0);

        $this->getJson('/api/coin-transactions')
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    private function createTaskFor(User $user, string $title, int $points): Task
    {
        $category = Category::factory()->create(['user_id' => $user->id]);

        return Task::query()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'title' => $title,
            'description' => null,
            'points' => $points,
            'is_active' => true,
        ]);
    }
}
