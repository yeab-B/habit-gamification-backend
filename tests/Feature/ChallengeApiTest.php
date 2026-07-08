<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Challenge;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ChallengeApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_challenge(): void
    {
        $user = User::factory()->create();
        $categories = Category::factory()->count(2)->create(['user_id' => $user->id]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/challenges', [
            'title' => '30 Days Healthy Life',
            'description' => 'Improve my health habits',
            'duration_days' => 30,
            'category_ids' => $categories->pluck('id')->all(),
        ]);

        $response->assertCreated()
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.title', '30 Days Healthy Life')
            ->assertJsonCount(2, 'data.categories');

        $this->assertDatabaseHas('challenges', [
            'user_id' => $user->id,
            'title' => '30 Days Healthy Life',
            'duration_days' => 30,
        ]);
    }

    public function test_challenge_requires_categories(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->postJson('/api/challenges', [
            'title' => '30 Days Healthy Life',
            'description' => 'Improve my health habits',
            'duration_days' => 30,
            'category_ids' => [],
        ])->assertStatus(422)
            ->assertJsonPath('status', false)
            ->assertJsonValidationErrors(['category_ids']);
    }

    public function test_user_can_view_challenges(): void
    {
        $owner = User::factory()->create();
        $viewer = User::factory()->create();
        $category = Category::factory()->create(['user_id' => $owner->id]);

        $challenge = Challenge::create([
            'user_id' => $owner->id,
            'title' => '30 Days Healthy Life',
            'description' => 'Improve my health habits',
            'duration_days' => 30,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(30)->toDateString(),
            'status' => 'active',
        ]);
        DB::table('challenge_categories')->insert([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'challenge_id' => $challenge->id,
            'category_id' => $category->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Sanctum::actingAs($viewer);

        $this->getJson('/api/challenges')
            ->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonCount(1, 'data');
    }

    public function test_user_can_view_challenge_details(): void
    {
        $owner = User::factory()->create();
        $viewer = User::factory()->create();
        $category = Category::factory()->create(['user_id' => $owner->id]);

        $challenge = Challenge::create([
            'user_id' => $owner->id,
            'title' => '30 Days Healthy Life',
            'description' => 'Improve my health habits',
            'duration_days' => 30,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(30)->toDateString(),
            'status' => 'active',
        ]);
        DB::table('challenge_categories')->insert([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'challenge_id' => $challenge->id,
            'category_id' => $category->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Sanctum::actingAs($viewer);

        $this->getJson('/api/challenges/' . $challenge->id)
            ->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.title', '30 Days Healthy Life')
            ->assertJsonCount(1, 'data.categories');
    }

    public function test_owner_can_update_challenge(): void
    {
        $owner = User::factory()->create();
        $category = Category::factory()->create(['user_id' => $owner->id]);
        $challenge = Challenge::create([
            'user_id' => $owner->id,
            'title' => 'Old Title',
            'description' => 'Old description',
            'duration_days' => 10,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(10)->toDateString(),
            'status' => 'active',
        ]);
        DB::table('challenge_categories')->insert([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'challenge_id' => $challenge->id,
            'category_id' => $category->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Sanctum::actingAs($owner);

        $this->putJson('/api/challenges/' . $challenge->id, [
            'title' => 'Updated Title',
            'description' => 'Updated description',
            'duration_days' => 15,
            'category_ids' => [$category->id],
        ])->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.title', 'Updated Title')
            ->assertJsonPath('data.duration_days', 15);
    }

    public function test_owner_can_delete_challenge(): void
    {
        $owner = User::factory()->create();
        $category = Category::factory()->create(['user_id' => $owner->id]);
        $challenge = Challenge::create([
            'user_id' => $owner->id,
            'title' => 'Delete Me',
            'description' => 'To be deleted',
            'duration_days' => 7,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(7)->toDateString(),
            'status' => 'active',
        ]);
        DB::table('challenge_categories')->insert([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'challenge_id' => $challenge->id,
            'category_id' => $category->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Sanctum::actingAs($owner);

        $this->deleteJson('/api/challenges/' . $challenge->id)
            ->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('message', 'Challenge deleted successfully');

        $this->assertSoftDeleted('challenges', ['id' => $challenge->id]);
    }

    public function test_user_can_join_challenge(): void
    {
        $owner = User::factory()->create();
        $participant = User::factory()->create();
        $category = Category::factory()->create(['user_id' => $owner->id]);
        $challenge = Challenge::create([
            'user_id' => $owner->id,
            'title' => 'Joinable Challenge',
            'description' => 'Join me',
            'duration_days' => 21,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(21)->toDateString(),
            'status' => 'active',
        ]);
        DB::table('challenge_categories')->insert([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'challenge_id' => $challenge->id,
            'category_id' => $category->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Sanctum::actingAs($participant);

        $this->postJson('/api/challenges/' . $challenge->id . '/join')
            ->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('message', 'Joined challenge successfully');

        $this->assertDatabaseHas('challenge_users', [
            'challenge_id' => $challenge->id,
            'user_id' => $participant->id,
        ]);
    }

    public function test_user_cannot_join_twice(): void
    {
        $owner = User::factory()->create();
        $participant = User::factory()->create();
        $category = Category::factory()->create(['user_id' => $owner->id]);
        $challenge = Challenge::create([
            'user_id' => $owner->id,
            'title' => 'Duplicate Join Challenge',
            'description' => 'Join once',
            'duration_days' => 21,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(21)->toDateString(),
            'status' => 'active',
        ]);
        DB::table('challenge_categories')->insert([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'challenge_id' => $challenge->id,
            'category_id' => $category->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Sanctum::actingAs($participant);

        $this->postJson('/api/challenges/' . $challenge->id . '/join')->assertOk();
        $this->postJson('/api/challenges/' . $challenge->id . '/join')
            ->assertStatus(422)
            ->assertJsonPath('status', false);
    }

    public function test_user_can_leave_challenge(): void
    {
        $owner = User::factory()->create();
        $participant = User::factory()->create();
        $category = Category::factory()->create(['user_id' => $owner->id]);
        $challenge = Challenge::create([
            'user_id' => $owner->id,
            'title' => 'Leaveable Challenge',
            'description' => 'Leave later',
            'duration_days' => 21,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(21)->toDateString(),
            'status' => 'active',
        ]);
        DB::table('challenge_categories')->insert([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'challenge_id' => $challenge->id,
            'category_id' => $category->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('challenge_users')->insert([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'challenge_id' => $challenge->id,
            'user_id' => $participant->id,
            'status' => 'accepted',
            'joined_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Sanctum::actingAs($participant);

        $this->deleteJson('/api/challenges/' . $challenge->id . '/leave')
            ->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('message', 'Left challenge successfully');

        $this->assertDatabaseMissing('challenge_users', [
            'challenge_id' => $challenge->id,
            'user_id' => $participant->id,
        ]);
    }

    public function test_owner_cannot_leave_own_challenge(): void
    {
        $owner = User::factory()->create();
        $category = Category::factory()->create(['user_id' => $owner->id]);
        $challenge = Challenge::create([
            'user_id' => $owner->id,
            'title' => 'Owner Challenge',
            'description' => 'Owner stays',
            'duration_days' => 14,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(14)->toDateString(),
            'status' => 'active',
        ]);
        DB::table('challenge_categories')->insert([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'challenge_id' => $challenge->id,
            'category_id' => $category->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Sanctum::actingAs($owner);

        $this->deleteJson('/api/challenges/' . $challenge->id . '/leave')
            ->assertStatus(422)
            ->assertJsonPath('status', false);
    }

    public function test_user_cannot_update_another_users_challenge(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $category = Category::factory()->create(['user_id' => $owner->id]);
        $challenge = Challenge::create([
            'user_id' => $owner->id,
            'title' => 'Protected Challenge',
            'description' => 'Not yours',
            'duration_days' => 14,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(14)->toDateString(),
            'status' => 'active',
        ]);
        DB::table('challenge_categories')->insert([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'challenge_id' => $challenge->id,
            'category_id' => $category->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Sanctum::actingAs($intruder);

        $this->putJson('/api/challenges/' . $challenge->id, [
            'title' => 'Hacked Title',
        ])->assertForbidden()
            ->assertJsonPath('status', false);
    }
}
