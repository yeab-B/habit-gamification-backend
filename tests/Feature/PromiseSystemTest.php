<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\CoinTransaction;
use App\Models\Promise;
use App\Models\Streak;
use App\Models\Task;
use App\Models\TaskCompletion;
use App\Models\User;
use App\Services\CoinService;
use App\Services\PromiseService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PromiseSystemTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_user_can_create_promise(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-07-10 09:00:00'));

        $user = User::factory()->create();
        $this->createTaskFor($user);

        Sanctum::actingAs($user);

        $this->postJson('/api/promises', ['reason' => 'I was sick today.'])
            ->assertCreated()
            ->assertJsonPath('status', true)
            ->assertJsonPath('message', 'Promise created successfully')
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.promise_date', '2026-07-10')
            ->assertJsonPath('data.validation_date', '2026-07-11');

        $this->assertDatabaseHas('promises', [
            'user_id' => $user->id,
            'status' => 'pending',
            'reason' => 'I was sick today.',
        ]);
    }

    public function test_user_cannot_create_two_pending_promises(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-07-10 09:00:00'));

        $user = User::factory()->create();
        $this->createTaskFor($user);

        Sanctum::actingAs($user);

        $this->postJson('/api/promises')->assertCreated();

        $this->postJson('/api/promises')
            ->assertStatus(422)
            ->assertJsonPath('status', false)
            ->assertJsonPath('message', 'You already have a pending promise.');
    }

    public function test_user_cannot_create_promise_after_completing_todays_habits(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-07-10 09:00:00'));

        $user = User::factory()->create();
        $task = $this->createTaskFor($user);
        $this->completeTaskFor($user, $task, '2026-07-10');

        Sanctum::actingAs($user);

        $this->postJson('/api/promises')
            ->assertStatus(422)
            ->assertJsonPath('message', 'You cannot create a promise after completing today\'s habits.');
    }

    public function test_promise_can_be_fulfilled(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-07-10 09:00:00'));

        $user = User::factory()->create();
        $task = $this->createTaskFor($user);
        $promise = app(PromiseService::class)->createPromise($user);

        Carbon::setTestNow(Carbon::parse('2026-07-11 21:00:00'));
        $this->completeTaskFor($user, $task, '2026-07-11');

        $validated = app(PromiseService::class)->validatePromise($promise->refresh());

        $this->assertSame('fulfilled', $validated->status);
        $this->assertNotNull($validated->validated_at);
    }

    public function test_promise_can_be_broken(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-07-10 09:00:00'));

        $user = User::factory()->create();
        $this->createTaskFor($user);
        $promise = app(PromiseService::class)->createPromise($user);

        Carbon::setTestNow(Carbon::parse('2026-07-11 21:00:00'));

        $validated = app(PromiseService::class)->validatePromise($promise->refresh());

        $this->assertSame('broken', $validated->status);
    }

    public function test_fulfilled_promise_protects_streak_and_awards_bonus_coins(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-07-10 09:00:00'));

        $user = User::factory()->create();
        $task = $this->createTaskFor($user);

        Streak::query()->create([
            'user_id' => $user->id,
            'current_streak' => 9,
            'longest_streak' => 9,
            'last_completed_date' => '2026-07-09',
        ]);

        $promise = app(PromiseService::class)->createPromise($user);

        Carbon::setTestNow(Carbon::parse('2026-07-11 21:00:00'));
        $this->completeTaskFor($user, $task, '2026-07-11');
        app(PromiseService::class)->validatePromise($promise->refresh());

        $this->assertDatabaseHas('streaks', [
            'user_id' => $user->id,
            'current_streak' => 11,
            'longest_streak' => 11,
        ]);

        $this->assertDatabaseHas('coin_transactions', [
            'user_id' => $user->id,
            'type' => 'bonus',
            'source' => 'promise_fulfilled',
            'amount' => 10,
        ]);
    }

    public function test_broken_promise_deducts_coins_and_updates_streak_correctly(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-07-10 09:00:00'));

        $user = User::factory()->create();
        $this->createTaskFor($user);
        app(CoinService::class)->earnCoins($user, 'seed', 150);

        Streak::query()->create([
            'user_id' => $user->id,
            'current_streak' => 9,
            'longest_streak' => 9,
            'last_completed_date' => '2026-07-09',
        ]);

        $promise = app(PromiseService::class)->createPromise($user);

        Carbon::setTestNow(Carbon::parse('2026-07-11 21:00:00'));
        app(PromiseService::class)->validatePromise($promise->refresh());

        $this->assertDatabaseHas('coin_transactions', [
            'user_id' => $user->id,
            'type' => 'penalty',
            'source' => 'promise_broken',
            'amount' => 100,
            'balance_after' => 50,
        ]);

        $this->assertDatabaseHas('streaks', [
            'user_id' => $user->id,
            'current_streak' => 0,
        ]);
    }

    public function test_users_only_access_their_own_promises(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-07-10 09:00:00'));

        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $this->createTaskFor($user);
        $this->createTaskFor($otherUser);

        app(PromiseService::class)->createPromise($otherUser);

        Sanctum::actingAs($user);

        $this->getJson('/api/promises')
            ->assertOk()
            ->assertJsonPath('status', true)
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

    private function completeTaskFor(User $user, Task $task, string $date): TaskCompletion
    {
        return TaskCompletion::query()->create([
            'user_id' => $user->id,
            'task_id' => $task->id,
            'completed_at' => Carbon::parse($date . ' 10:00:00'),
            'completion_date' => $date,
        ]);
    }
}
