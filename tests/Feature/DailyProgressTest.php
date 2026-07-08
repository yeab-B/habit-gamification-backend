<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\DailyCheckin;
use App\Models\Task;
use App\Models\TaskCompletion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DailyProgressTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_complete_task(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create(['user_id' => $user->id]);
        $task = Task::create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'title' => 'Exercise 30 minutes',
            'description' => 'Morning workout',
            'points' => 10,
            'is_active' => true,
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/tasks/' . $task->id . '/complete');

        $response->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('message', 'Task completed successfully');

        $this->assertDatabaseHas('task_completions', [
            'user_id' => $user->id,
            'task_id' => $task->id,
        ]);

        $this->assertTrue(
            TaskCompletion::query()
                ->where('user_id', $user->id)
                ->where('task_id', $task->id)
                ->whereDate('completion_date', now()->toDateString())
                ->exists()
        );

        $this->assertDatabaseHas('daily_checkins', [
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'tasks_completed' => 1,
            'total_points' => 10,
            'is_completed' => true,
        ]);
    }

    public function test_user_cannot_complete_task_twice(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create(['user_id' => $user->id]);
        $task = Task::create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'title' => 'Drink water',
            'description' => null,
            'points' => 5,
            'is_active' => true,
        ]);

        Sanctum::actingAs($user);

        $this->postJson('/api/tasks/' . $task->id . '/complete')->assertOk();

        $this->postJson('/api/tasks/' . $task->id . '/complete')
            ->assertStatus(422)
            ->assertJsonPath('status', false)
            ->assertJsonPath('message', 'Task already completed today');
    }

    public function test_user_cannot_complete_another_users_task(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $category = Category::factory()->create(['user_id' => $owner->id]);
        $task = Task::create([
            'user_id' => $owner->id,
            'category_id' => $category->id,
            'title' => 'Owner task',
            'description' => null,
            'points' => 8,
            'is_active' => true,
        ]);

        Sanctum::actingAs($intruder);

        $this->postJson('/api/tasks/' . $task->id . '/complete')
            ->assertForbidden()
            ->assertJsonPath('status', false);
    }

    public function test_daily_checkin_created_automatically(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create(['user_id' => $user->id]);
        $task = Task::create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'title' => 'Read 10 pages',
            'description' => null,
            'points' => 7,
            'is_active' => true,
        ]);

        Sanctum::actingAs($user);

        $this->postJson('/api/tasks/' . $task->id . '/complete')->assertOk();

        $this->getJson('/api/daily-checkins')
            ->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.0.tasks_completed', 1)
            ->assertJsonPath('data.0.total_points', 7);
    }

    public function test_today_summary_returns_correct_data(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create(['user_id' => $user->id]);
        $taskOne = Task::create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'title' => 'Exercise',
            'description' => null,
            'points' => 10,
            'is_active' => true,
        ]);
        Task::create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'title' => 'Stretch',
            'description' => null,
            'points' => 15,
            'is_active' => true,
        ]);

        Sanctum::actingAs($user);

        $this->postJson('/api/tasks/' . $taskOne->id . '/complete')->assertOk();

        $this->getJson('/api/today')
            ->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.completed_tasks', 1)
            ->assertJsonPath('data.remaining_tasks', 1)
            ->assertJsonPath('data.earned_points', 10)
            ->assertJsonPath('data.completion_percentage', 50);
    }
}
