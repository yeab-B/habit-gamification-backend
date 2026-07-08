<?php

namespace Tests\Feature;

use App\Models\Freeze;
use App\Models\Friendship;
use App\Models\Streak;
use App\Models\User;
use App\Services\CoinService;
use App\Services\FreezeService;
use App\Services\StreakService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class FreezeSystemTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_user_can_send_freeze_to_a_friend(): void
    {
        $sender = User::factory()->create();
        $receiver = User::factory()->create();
        $this->acceptedFriendship($sender, $receiver);
        app(CoinService::class)->earnCoins($sender, 'seed', 10);

        Sanctum::actingAs($sender);

        $this->postJson('/api/freezes', [
            'receiver_id' => $receiver->id,
            'reason' => 'Get well soon!',
        ])->assertCreated()
            ->assertJsonPath('status', true)
            ->assertJsonPath('message', 'Freeze sent successfully')
            ->assertJsonPath('data.status', 'available')
            ->assertJsonPath('data.cost', 3)
            ->assertJsonPath('data.reason', 'Get well soon!');

        $this->assertDatabaseHas('freezes', [
            'sender_id' => $sender->id,
            'receiver_id' => $receiver->id,
            'status' => 'available',
            'cost' => 3,
        ]);
    }

    public function test_user_cannot_send_freeze_to_self(): void
    {
        $user = User::factory()->create();
        app(CoinService::class)->earnCoins($user, 'seed', 10);

        Sanctum::actingAs($user);

        $this->postJson('/api/freezes', [
            'receiver_id' => $user->id,
        ])->assertStatus(422)
            ->assertJsonPath('message', 'You cannot send a freeze to yourself.');
    }

    public function test_user_cannot_send_freeze_to_non_friend(): void
    {
        $sender = User::factory()->create();
        $receiver = User::factory()->create();
        app(CoinService::class)->earnCoins($sender, 'seed', 10);

        Sanctum::actingAs($sender);

        $this->postJson('/api/freezes', [
            'receiver_id' => $receiver->id,
        ])->assertStatus(422)
            ->assertJsonPath('message', 'You can only send freezes to accepted friends.');
    }

    public function test_user_cannot_send_freeze_without_enough_coins(): void
    {
        $sender = User::factory()->create();
        $receiver = User::factory()->create();
        $this->acceptedFriendship($sender, $receiver);

        Sanctum::actingAs($sender);

        $this->postJson('/api/freezes', [
            'receiver_id' => $receiver->id,
        ])->assertStatus(422)
            ->assertJsonPath('message', 'Insufficient coin balance.');
    }

    public function test_available_freeze_protects_streak_and_becomes_used(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-07-12 09:00:00'));

        $sender = User::factory()->create();
        $receiver = User::factory()->create();
        $this->acceptedFriendship($sender, $receiver);
        app(CoinService::class)->earnCoins($sender, 'seed', 10);
        app(FreezeService::class)->sendFreeze($sender, $receiver);

        Streak::query()->create([
            'user_id' => $receiver->id,
            'current_streak' => 5,
            'longest_streak' => 5,
            'last_completed_date' => '2026-07-10',
        ]);

        $streak = app(StreakService::class)->calculateStreak($receiver);

        $this->assertSame(5, $streak->current_streak);

        $this->assertDatabaseHas('freezes', [
            'receiver_id' => $receiver->id,
            'status' => 'used',
        ]);

        $this->assertNotNull(Freeze::query()->where('receiver_id', $receiver->id)->first()->used_at);
    }

    public function test_used_freeze_cannot_be_reused(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-07-12 09:00:00'));

        $sender = User::factory()->create();
        $receiver = User::factory()->create();
        $this->acceptedFriendship($sender, $receiver);

        Freeze::query()->create([
            'sender_id' => $sender->id,
            'receiver_id' => $receiver->id,
            'status' => 'used',
            'cost' => 3,
            'used_at' => now()->subDay(),
        ]);

        Streak::query()->create([
            'user_id' => $receiver->id,
            'current_streak' => 5,
            'longest_streak' => 5,
            'last_completed_date' => '2026-07-10',
        ]);

        $streak = app(StreakService::class)->calculateStreak($receiver);

        $this->assertSame(0, $streak->current_streak);
    }

    public function test_oldest_available_freeze_is_consumed_first(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-07-12 09:00:00'));

        $sender = User::factory()->create();
        $receiver = User::factory()->create();
        $this->acceptedFriendship($sender, $receiver);

        $oldest = Freeze::query()->create([
            'sender_id' => $sender->id,
            'receiver_id' => $receiver->id,
            'status' => 'available',
            'cost' => 3,
            'created_at' => now()->subDays(2),
            'updated_at' => now()->subDays(2),
        ]);

        $newest = Freeze::query()->create([
            'sender_id' => $sender->id,
            'receiver_id' => $receiver->id,
            'status' => 'available',
            'cost' => 3,
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ]);

        Streak::query()->create([
            'user_id' => $receiver->id,
            'current_streak' => 5,
            'longest_streak' => 5,
            'last_completed_date' => '2026-07-10',
        ]);

        app(StreakService::class)->calculateStreak($receiver);

        $this->assertSame('used', $oldest->refresh()->status);
        $this->assertSame('available', $newest->refresh()->status);
    }

    public function test_sending_freeze_deducts_coins_and_creates_transaction(): void
    {
        $sender = User::factory()->create();
        $receiver = User::factory()->create();
        $this->acceptedFriendship($sender, $receiver);
        app(CoinService::class)->earnCoins($sender, 'seed', 10);

        app(FreezeService::class)->sendFreeze($sender, $receiver);

        $this->assertDatabaseHas('coin_transactions', [
            'user_id' => $sender->id,
            'type' => 'spend',
            'source' => 'freeze',
            'amount' => 3,
            'balance_after' => 7,
            'description' => 'Freeze sent',
        ]);
    }

    public function test_users_only_view_their_own_freeze_history(): void
    {
        $user = User::factory()->create();
        $friend = User::factory()->create();
        $otherUser = User::factory()->create();
        $otherFriend = User::factory()->create();

        $this->acceptedFriendship($user, $friend);
        $this->acceptedFriendship($otherUser, $otherFriend);

        app(CoinService::class)->earnCoins($user, 'seed', 10);
        app(CoinService::class)->earnCoins($otherUser, 'seed', 10);
        app(FreezeService::class)->sendFreeze($user, $friend);
        app(FreezeService::class)->sendFreeze($otherUser, $otherFriend);

        Sanctum::actingAs($user);

        $this->getJson('/api/freezes')
            ->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.sender.id', $user->id);
    }

    private function acceptedFriendship(User $firstUser, User $secondUser): Friendship
    {
        return Friendship::query()->create([
            'sender_id' => $firstUser->id,
            'receiver_id' => $secondUser->id,
            'status' => 'accepted',
            'responded_at' => now(),
        ]);
    }
}
