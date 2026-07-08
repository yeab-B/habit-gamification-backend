<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TaskApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_task(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create(['user_id' => $user->id]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/tasks', [
            'category_id' => $category->id,
            'title' => 'Exercise 30 minutes',
            'description' => 'Morning workout',
            'points' => 10,
        ]);

        $response->assertCreated()
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.title', 'Exercise 30 minutes')
            ->assertJsonPath('data.user_id', $user->id);

        $this->assertDatabaseHas('tasks', [
            'user_id' => $user->id,
            'category_id' => $category->id,
            'title' => 'Exercise 30 minutes',
        ]);
    }

    public function test_user_can_list_tasks(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $userCategory = Category::factory()->create(['user_id' => $user->id]);
        $otherCategory = Category::factory()->create(['user_id' => $otherUser->id]);

        Task::factory()->create([
            'user_id' => $user->id,
            'category_id' => $userCategory->id,
            'title' => 'Own Task',
        ]);

        Task::factory()->create([
            'user_id' => $otherUser->id,
            'category_id' => $otherCategory->id,
            'title' => 'Other Task',
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/tasks');

        $response->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Own Task');
    }

    public function test_user_can_update_task(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create(['user_id' => $user->id]);
        $task = Task::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
        ]);

        Sanctum::actingAs($user);

        $response = $this->putJson("/api/tasks/{$task->id}", [
            'title' => 'Updated Task',
            'points' => 25,
            'is_active' => false,
        ]);

        $response->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.title', 'Updated Task')
            ->assertJsonPath('data.points', 25)
            ->assertJsonPath('data.is_active', false);
    }

    public function test_user_can_delete_task(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create(['user_id' => $user->id]);
        $task = Task::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
        ]);

        Sanctum::actingAs($user);

        $response = $this->deleteJson("/api/tasks/{$task->id}");

        $response->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('message', 'Task deleted successfully');

        $this->assertSoftDeleted('tasks', ['id' => $task->id]);
    }

    public function test_user_cannot_create_task_under_another_users_category(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $category = Category::factory()->create(['user_id' => $owner->id]);

        Sanctum::actingAs($intruder);

        $response = $this->postJson('/api/tasks', [
            'category_id' => $category->id,
            'title' => 'Illegal Task',
            'description' => 'Should fail',
            'points' => 10,
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('status', false)
            ->assertJsonValidationErrors(['category_id']);
    }
}
