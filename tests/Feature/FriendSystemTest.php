<?php

namespace Tests\Feature;

use App\Models\Friendship;
use App\Models\Streak;
use App\Models\User;
use App\Services\CoinService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class FriendSystemTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_search_users(): void
    {
        $user = User::factory()->create(['name' => 'Current User']);
        User::factory()->create(['name' => 'John Walker']);
        User::factory()->create(['name' => 'Sara Smith']);

        Sanctum::actingAs($user);

        $this->getJson('/api/users/search?search=john')
            ->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'John Walker');
    }

    public function test_current_user_excluded_from_search(): void
    {
        $user = User::factory()->create(['name' => 'John Current']);
        User::factory()->create(['name' => 'John Friend']);

        Sanctum::actingAs($user);

        $this->getJson('/api/users/search?search=john')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'John Friend');
    }

    public function test_user_can_send_friend_request(): void
    {
        $sender = User::factory()->create();
        $receiver = User::factory()->create();

        Sanctum::actingAs($sender);

        $this->postJson('/api/friends/request', [
            'user_id' => $receiver->id,
        ])->assertCreated()
            ->assertJsonPath('status', true)
            ->assertJsonPath('message', 'Friend request sent successfully')
            ->assertJsonPath('data.status', 'pending');

        $this->assertDatabaseHas('friendships', [
            'sender_id' => $sender->id,
            'receiver_id' => $receiver->id,
            'status' => 'pending',
        ]);
    }

    public function test_user_cannot_send_request_to_self(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $this->postJson('/api/friends/request', [
            'user_id' => $user->id,
        ])->assertStatus(422)
            ->assertJsonPath('status', false)
            ->assertJsonPath('message', 'You cannot send a friend request to yourself.');
    }

    public function test_user_cannot_send_duplicate_request(): void
    {
        $sender = User::factory()->create();
        $receiver = User::factory()->create();

        Friendship::query()->create([
            'sender_id' => $sender->id,
            'receiver_id' => $receiver->id,
            'status' => 'pending',
        ]);

        Sanctum::actingAs($sender);

        $this->postJson('/api/friends/request', [
            'user_id' => $receiver->id,
        ])->assertStatus(422)
            ->assertJsonPath('message', 'A friendship or pending request already exists.');
    }

    public function test_user_cannot_send_request_to_existing_friend(): void
    {
        $sender = User::factory()->create();
        $receiver = User::factory()->create();

        Friendship::query()->create([
            'sender_id' => $receiver->id,
            'receiver_id' => $sender->id,
            'status' => 'accepted',
            'responded_at' => now(),
        ]);

        Sanctum::actingAs($sender);

        $this->postJson('/api/friends/request', [
            'user_id' => $receiver->id,
        ])->assertStatus(422)
            ->assertJsonPath('message', 'A friendship or pending request already exists.');
    }

    public function test_receiver_can_accept_request(): void
    {
        $sender = User::factory()->create();
        $receiver = User::factory()->create();
        $friendship = $this->pendingFriendship($sender, $receiver);

        Sanctum::actingAs($receiver);

        $this->postJson('/api/friends/accept', [
            'friendship_id' => $friendship->id,
        ])->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.status', 'accepted');

        $this->assertDatabaseHas('friendships', [
            'id' => $friendship->id,
            'status' => 'accepted',
        ]);
    }

    public function test_receiver_can_reject_request(): void
    {
        $sender = User::factory()->create();
        $receiver = User::factory()->create();
        $friendship = $this->pendingFriendship($sender, $receiver);

        Sanctum::actingAs($receiver);

        $this->postJson('/api/friends/reject', [
            'friendship_id' => $friendship->id,
        ])->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.status', 'rejected');
    }

    public function test_other_users_cannot_accept_request(): void
    {
        $sender = User::factory()->create();
        $receiver = User::factory()->create();
        $otherUser = User::factory()->create();
        $friendship = $this->pendingFriendship($sender, $receiver);

        Sanctum::actingAs($otherUser);

        $this->postJson('/api/friends/accept', [
            'friendship_id' => $friendship->id,
        ])->assertForbidden();
    }

    public function test_friend_list_returns_accepted_friends_only(): void
    {
        $user = User::factory()->create();
        $acceptedFriend = User::factory()->create(['name' => 'Accepted Friend']);
        $pendingFriend = User::factory()->create(['name' => 'Pending Friend']);

        $friendship = Friendship::query()->create([
            'sender_id' => $acceptedFriend->id,
            'receiver_id' => $user->id,
            'status' => 'accepted',
            'responded_at' => now(),
        ]);

        $this->pendingFriendship($user, $pendingFriend);

        Streak::query()->create([
            'user_id' => $acceptedFriend->id,
            'current_streak' => 4,
            'longest_streak' => 6,
            'last_completed_date' => now()->toDateString(),
        ]);
        app(CoinService::class)->earnCoins($acceptedFriend, 'task_completion', 15);

        Sanctum::actingAs($user);

        $this->getJson('/api/friends')
            ->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Accepted Friend')
            ->assertJsonPath('data.0.current_streak', 4)
            ->assertJsonPath('data.0.coin_balance', 15)
            ->assertJsonPath('data.0.friendship_id', $friendship->id);
    }

    public function test_user_can_remove_friend_and_soft_delete_works(): void
    {
        $user = User::factory()->create();
        $friend = User::factory()->create();
        $friendship = Friendship::query()->create([
            'sender_id' => $user->id,
            'receiver_id' => $friend->id,
            'status' => 'accepted',
            'responded_at' => now(),
        ]);

        Sanctum::actingAs($user);

        $this->deleteJson('/api/friends/' . $friendship->id)
            ->assertOk()
            ->assertJsonPath('message', 'Friend removed successfully');

        $this->assertSoftDeleted('friendships', [
            'id' => $friendship->id,
        ]);
    }

    public function test_user_cannot_manipulate_another_users_friendships(): void
    {
        $sender = User::factory()->create();
        $receiver = User::factory()->create();
        $otherUser = User::factory()->create();
        $friendship = Friendship::query()->create([
            'sender_id' => $sender->id,
            'receiver_id' => $receiver->id,
            'status' => 'accepted',
            'responded_at' => now(),
        ]);

        Sanctum::actingAs($otherUser);

        $this->deleteJson('/api/friends/' . $friendship->id)
            ->assertForbidden();
    }

    private function pendingFriendship(User $sender, User $receiver): Friendship
    {
        return Friendship::query()->create([
            'sender_id' => $sender->id,
            'receiver_id' => $receiver->id,
            'status' => 'pending',
        ]);
    }
}
