<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CategoryApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_category(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/categories', [
            'name' => 'Fitness',
            'description' => 'Health and exercise habits',
            'icon' => '💪',
            'color' => 'blue',
        ]);

        $response->assertCreated()
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.name', 'Fitness');

        $this->assertDatabaseHas('categories', [
            'user_id' => $user->id,
            'name' => 'Fitness',
        ]);
    }

    public function test_user_can_view_categories(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        Category::factory()->create(['user_id' => $user->id, 'name' => 'Fitness']);
        Category::factory()->create(['user_id' => $otherUser->id, 'name' => 'Private Category']);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/categories');

        $response->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Fitness');
    }

    public function test_user_can_update_category(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create(['user_id' => $user->id]);

        Sanctum::actingAs($user);

        $response = $this->putJson("/api/categories/{$category->id}", [
            'name' => 'Updated Fitness',
            'color' => 'green',
        ]);

        $response->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.name', 'Updated Fitness')
            ->assertJsonPath('data.color', 'green');
    }

    public function test_user_can_delete_category(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create(['user_id' => $user->id]);
        $task = Task::factory()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
        ]);

        Sanctum::actingAs($user);

        $response = $this->deleteJson("/api/categories/{$category->id}");

        $response->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('message', 'Category deleted successfully');

        $this->assertSoftDeleted('categories', ['id' => $category->id]);
        $this->assertSoftDeleted('tasks', ['id' => $task->id]);
    }

    public function test_user_cannot_access_another_users_category(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $category = Category::factory()->create(['user_id' => $owner->id]);

        Sanctum::actingAs($intruder);

        $response = $this->putJson("/api/categories/{$category->id}", [
            'name' => 'Hacked',
        ]);

        $response->assertForbidden()
            ->assertJsonPath('status', false);
    }
}
